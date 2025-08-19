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
use Exception;
use Illuminate\Support\Facades\DB;

class GenerateService
{
    public function generate(GenerateRequest $request): array
    {
        $exist = Project::where('name', $request->name)->first();

        if ($exist) {
            throw new Exception("Nama project sudah digunakan");
        }

        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // create project
            $project = Project::create($request->all());

            // duplicate hse doc
            HseDoc::each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                $duplicate->setTable('hse_docs');
                $duplicate->project_uuid = $project->uuid;
                $duplicate->hse_doc_uuid = $row->uuid;
                $duplicate->save();
            });

            // duplicate scope standart
            ScopeStandart::select('uuid', 'name', 'link', 'category', 'sub_bidang_uuid')
                ->where('inspection_type_uuid', $request->inspection_type_uuid)
                ->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('scope_standarts');
                    $duplicate->project_uuid = $project->uuid;
                    $duplicate->save();
                });

            // duplicate equipment
            Equipment::select('uuid', 'scope_standart_uuid', 'name', 'link_ik1', 'link_ik2')
                ->whereHas('scopeStandart', fn($query) => $query->where('inspection_type_uuid', $request->inspection_type_uuid))
                ->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('equipment');
                    $duplicate->project_uuid = $project->uuid;
                    $duplicate->save();
                });

            // duplicate activity
            Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                ->whereHas('equipment.scopeStandart', fn($query) => $query->where('inspection_type_uuid', $request->inspection_type_uuid))
                ->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('activities');
                    $duplicate->project_uuid = $project->uuid;
                    $duplicate->save();
                });

            // duplicate consumable material
            ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('inspection_type_uuid', $request->inspection_type_uuid))
                ->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('cons_mat_stds');
                    $duplicate->project_uuid = $project->uuid;
                    $duplicate->save();
                });

            // duplicate part std
            PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('inspection_type_uuid', $request->inspection_type_uuid))
                ->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('part_stds');
                    $duplicate->project_uuid = $project->uuid;
                    $duplicate->save();
                });

            // duplicate manpower std
            ManpowerStd::select('uuid', 'activity_uuid', 'manpower_uuid', 'qty')
                ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('inspection_type_uuid', $request->inspection_type_uuid))
                ->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('manpower_stds');
                    $duplicate->project_uuid = $project->uuid;
                    $duplicate->save();
                });

            // TODO duplicate Qc Plan
            // QcPlan::insert([
            //     [
            //         'uuid' => Str::uuid(),
            //         'name' => 'QC Plan Mekanik',
            //         'project_uuid' => $project->uuid
            //     ],
            //     [
            //         'uuid' => Str::uuid(),
            //         'name' => 'QC Plan Listrik',
            //         'project_uuid' => $project->uuid
            //     ],
            //     [
            //         'uuid' => Str::uuid(),
            //         'name' => 'QC Plan Instrument',
            //         'project_uuid' => $project->uuid
            //     ]
            // ]);

            // duplicate additional scope
            AdditionalScope::select('uuid', 'name', 'sequence_uuid')
                ->where('inspection_type_uuid', $request->inspection_type_uuid)
                ->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('additional_scopes');
                    $duplicate->project_uuid = $project->uuid;
                    $duplicate->save();

                    // duplicate scope standart
                    ScopeStandart::select('uuid', 'name', 'link', 'category', 'sub_bidang_uuid')
                        ->where('additional_scope_uuid', $duplicate->uuid)
                        ->each(function ($row) use ($project) {
                            $duplicate = $row->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('scope_standarts');
                            $duplicate->project_uuid = $project->uuid;
                            $duplicate->save();
                        });


                    // duplicate equipment
                    Equipment::select('uuid', 'scope_standart_uuid', 'name', 'link_ik1', 'link_ik2')
                        ->whereHas('scopeStandart', fn($query) => $query->where('additional_scope_uuid', $duplicate->uuid))
                        ->each(function ($row) use ($project) {
                            $duplicate = $row->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('equipment');
                            $duplicate->project_uuid = $project->uuid;
                            $duplicate->save();
                        });

                    // duplicate activity
                    Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                        ->whereHas('equipment.scopeStandart', fn($query) => $query->where('additional_scope_uuid', $duplicate->uuid))
                        ->each(function ($row) use ($project) {
                            $duplicate = $row->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('activities');
                            $duplicate->project_uuid = $project->uuid;
                            $duplicate->save();
                        });

                    // duplicate consumable material
                    ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                        ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('additional_scope_uuid', $duplicate->uuid))
                        ->each(function ($row) use ($project) {
                            $duplicate = $row->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('cons_mat_stds');
                            $duplicate->project_uuid = $project->uuid;
                            $duplicate->save();
                        });

                    // duplicate part std
                    PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                        ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('additional_scope_uuid', $duplicate->uuid))
                        ->each(function ($row) use ($project) {
                            $duplicate = $row->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('part_stds');
                            $duplicate->project_uuid = $project->uuid;
                            $duplicate->save();
                        });

                    // duplicate manpower std
                    ManpowerStd::select('uuid', 'activity_uuid', 'manpower_uuid', 'qty')
                        ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('additional_scope_uuid', $duplicate->uuid))
                        ->each(function ($row) use ($project) {
                            $duplicate = $row->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('manpower_stds');
                            $duplicate->project_uuid = $project->uuid;
                            $duplicate->save();
                        });
                });
        });

        return [
            'message' => 'Project created successfully'
        ];
    }
}
