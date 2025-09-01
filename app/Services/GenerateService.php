<?php

namespace App\Services;

use App\Enums\ConnectionEnum;
use App\Http\Requests\GenerateRequest;
use App\Models\Activity;
use App\Models\AdditionalScope;
use App\Models\ConsMatStd;
use App\Models\Equipment;
use App\Models\HseDoc;
use App\Models\ManpowerStd;
use App\Models\PartStd;
use App\Models\ScopeStandart;
use App\Models\Transaction\Project;
use App\Models\Transaction\QcPlan;
use Exception;
use Illuminate\Support\Facades\DB;

class GenerateService
{
    public function generate(GenerateRequest $request)
    {
        $exist = Project::where('name', $request->name)->first();

        if ($exist) {
            throw new Exception("Nama project sudah digunakan");
        }

        $generated = DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // create project
            $project = Project::create($request->all());

            // duplicate hse doc
            HseDoc::select('uuid')->each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                $duplicate->setTable('hse_docs');
                $duplicate->original_uuid = $row->uuid;
                $duplicate->project_uuid = $project->uuid;
                $duplicate->hse_doc_uuid = $row->uuid;
                $duplicate->save();
            });

            // duplicate scope standart
            ScopeStandart::select('uuid', 'name', 'link', 'category', 'sub_bidang_uuid')
                ->where('inspection_type_uuid', $request->inspection_type_uuid)
                ->each(function ($scope) use ($project) {
                    $duplicateScope = $scope->replicate();
                    $duplicateScope->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicateScope->setTable('scope_standarts');
                    $duplicateScope->original_uuid = $scope->uuid;
                    $duplicateScope->project_uuid = $project->uuid;
                    $duplicateScope->save();

                    // duplicate equipment
                    // duplicate equipment
                    Equipment::select('uuid', 'scope_standart_uuid', 'name', 'link_ik1', 'link_ik2')
                        ->whereHas('scopeStandart', fn($query) => $query->where('scope_standart_uuid', $scope->uuid))
                        ->each(function ($equipment) use ($duplicateScope) {
                        $duplicateEquipment = $equipment->replicate();
                        $duplicateEquipment->setConnection(ConnectionEnum::TRANSACTION->value);
                        $duplicateEquipment->setTable('equipment');
                        $duplicateEquipment->original_uuid = $equipment->uuid;
                        $duplicateEquipment->scope_standart_uuid = $duplicateScope->uuid;
                        $duplicateEquipment->save();

                        // duplicate activity
                        Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                            ->whereHas('equipment.scopeStandart', fn($query) => $query->where('equipment_uuid', $equipment->uuid))
                            ->each(function ($activity) use ($duplicateEquipment) {
                            $duplicateActivity = $activity->replicate();
                            $duplicateActivity->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicateActivity->setTable('activities');
                            $duplicateActivity->original_uuid = $activity->uuid;
                            $duplicateActivity->equipment_uuid = $duplicateEquipment->uuid;
                            $duplicateActivity->save();

                            // duplicate consumable material
                            ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                                ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                ->each(function ($row) use ($duplicateActivity) {
                                $duplicate = $row->replicate();
                                $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                $duplicate->setTable('cons_mat_stds');
                                $duplicate->original_uuid = $row->uuid;
                                $duplicate->activity_uuid = $duplicateActivity->uuid;
                                $duplicate->save();
                            });

                            // duplicate part std
                            PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                                ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                ->each(function ($row) use ($duplicateActivity) {
                                $duplicate = $row->replicate();
                                $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                $duplicate->setTable('part_stds');
                                $duplicate->activity_uuid = $duplicateActivity->uuid;
                                $duplicate->original_uuid = $row->uuid;
                                $duplicate->save();
                            });

                            // duplicate manpower std
                            ManpowerStd::select('uuid', 'activity_uuid', 'manpower_uuid', 'qty')
                                ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                ->each(function ($row) use ($duplicateActivity) {
                                $duplicate = $row->replicate();
                                $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                $duplicate->setTable('manpower_stds');
                                $duplicate->original_uuid = $row->uuid;
                                $duplicate->activity_uuid = $duplicateActivity->uuid;
                                $duplicate->save();
                            });
                        });
                    });
                });

            // TODO duplicate Qc Plan
            collect([
                [
                    'name' => 'QC Plan Mekanik',
                ],
                [
                    'name' => 'QC Plan Listrik',
                ],
                [
                    'name' => 'QC Plan Instrument',
                ]
            ])->each(function ($row) use ($project) {
                QcPlan::create([
                    'name' => $row['name'],
                    'project_uuid' => $project->uuid
                ]);
            });

            // duplicate additional scope
            AdditionalScope::select('uuid', 'name', 'sequence_uuid')
                ->where('inspection_type_uuid', $request->inspection_type_uuid)
                ->each(function ($addScope) use ($project) {
                    $duplicateAdScope = $addScope->replicate();
                    $duplicateAdScope->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicateAdScope->setTable('additional_scopes');
                    $duplicateAdScope->project_uuid = $project->uuid;
                    $duplicateAdScope->original_uuid = $addScope->uuid;
                    $duplicateAdScope->save();

                    // duplicate scope standart
                    ScopeStandart::select('uuid', 'name', 'link', 'category', 'sub_bidang_uuid')
                        ->where('additional_scope_uuid', $addScope->uuid)
                        ->each(function ($scope) use ($duplicateAdScope) {
                        $duplicateScope = $scope->replicate();
                        $duplicateScope->setConnection(ConnectionEnum::TRANSACTION->value);
                        $duplicateScope->setTable('scope_standarts');
                        $duplicateScope->additional_scope_uuid = $duplicateAdScope->uuid;
                        $duplicateScope->original_uuid = $scope->uuid;
                        $duplicateScope->save();

                        // duplicate equipment
                        Equipment::select('uuid', 'scope_standart_uuid', 'name', 'link_ik1', 'link_ik2')
                            ->whereHas('scopeStandart', fn($query) => $query->where('scope_standart_uuid', $scope->uuid))
                            ->each(function ($equipment) use ($duplicateScope) {
                            $duplicateEquipment = $equipment->replicate();
                            $duplicateEquipment->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicateEquipment->setTable('equipment');
                            $duplicateEquipment->original_uuid = $equipment->uuid;
                            $duplicateEquipment->scope_standart_uuid = $duplicateScope->uuid;
                            $duplicateEquipment->save();

                            // duplicate activity
                            Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                                ->whereHas('equipment.scopeStandart', fn($query) => $query->where('equipment_uuid', $equipment->uuid))
                                ->each(function ($activity) use ($duplicateEquipment) {
                                $duplicateActivity = $activity->replicate();
                                $duplicateActivity->setConnection(ConnectionEnum::TRANSACTION->value);
                                $duplicateActivity->setTable('activities');
                                $duplicateActivity->original_uuid = $activity->uuid;
                                $duplicateActivity->equipment_uuid = $duplicateEquipment->uuid;
                                $duplicateActivity->save();

                                // duplicate consumable material
                                ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                    ->each(function ($row) use ($duplicateActivity) {
                                    $duplicate = $row->replicate();
                                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                    $duplicate->setTable('cons_mat_stds');
                                    $duplicate->original_uuid = $row->uuid;
                                    $duplicate->activity_uuid = $duplicateActivity->uuid;
                                    $duplicate->save();
                                });

                                // duplicate part std
                                PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                    ->each(function ($row) use ($duplicateActivity) {
                                    $duplicate = $row->replicate();
                                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                    $duplicate->setTable('part_stds');
                                    $duplicate->original_uuid = $row->uuid;
                                    $duplicate->activity_uuid = $duplicateActivity->uuid;
                                    $duplicate->save();
                                });

                                // duplicate manpower std
                                ManpowerStd::select('uuid', 'activity_uuid', 'manpower_uuid', 'qty')
                                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                    ->each(function ($row) use ($duplicateActivity) {
                                    $duplicate = $row->replicate();
                                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                    $duplicate->setTable('manpower_stds');
                                    $duplicate->original_uuid = $row->uuid;
                                    $duplicate->activity_uuid = $duplicateActivity->uuid;
                                    $duplicate->save();
                                });
                            });
                        });
                    });
                });

            return $project;
        });

        return $generated;
    }
}
