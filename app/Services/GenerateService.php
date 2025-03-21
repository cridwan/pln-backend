<?php

namespace App\Services;

use App\Http\Requests\GenerateRequest;
use App\Models\AdditionalScope;
use App\Models\ConsMat;
use App\Models\DetailScopeStandart;
use App\Models\Hse;
use App\Models\Manpower;
use App\Models\Part;
use App\Models\ScopeStandart;
use App\Models\Sequence;
use App\Models\SequenceAnimation;
use App\Models\Tools;
use App\Models\Transaction\Project;
use App\Models\Transaction\QcPlan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateService
{
    public function generate(GenerateRequest $request): Project
    {
        DB::beginTransaction();
        try {
            $exist = Project::where('name', $request->name)->first();

            if ($exist) {
                throw new Exception("Nama project sudah digunakan");
            }

            $project = Project::create($request->all());

            Part::select('name', 'qty', 'global_unit_uuid', 'size', 'no_drawing', 'location')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection('transaction');
                $duplicate->setTable('parts');
                $duplicate->project_uuid = $project->uuid;
                $duplicate->save();
            });

            Tools::select('name', 'qty', 'global_unit_uuid', 'additional_scope_uuid', 'section')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection('transaction');
                $duplicate->setTable('tools');
                $duplicate->project_uuid = $project->uuid;
                $duplicate->save();
            });

            Sequence::select('uuid', 'name', 'link')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection('transaction');
                $duplicate->setTable('sequences');
                $duplicate->project_uuid = $project->uuid;
                $duplicate->save();
            });

            ScopeStandart::select('uuid', 'name', 'link', 'category')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
                $scopeStandart = $row->replicate();
                $scopeStandart->setConnection('transaction');
                $scopeStandart->setTable('scope_standarts');
                $scopeStandart->project_uuid = $project->uuid;
                $scopeStandart->save();

                DetailScopeStandart::select('uuid', 'name', 'link')->where('scope_standart_uuid', $row->uuid)->each(function ($row) use ($scopeStandart) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('detail_scope_standarts');
                    $duplicate->scope_standart_uuid = $scopeStandart->uuid;
                    $duplicate->save();
                });
            });

            Manpower::select('uuid', 'name', 'qty', 'type')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection('transaction');
                $duplicate->setTable('manpowers');
                $duplicate->project_uuid = $project->uuid;
                $duplicate->save();
            });

            ConsMat::select('uuid', 'name', 'merk', 'qty', 'global_unit_uuid')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection('transaction');
                $duplicate->setTable('const_mats');
                $duplicate->project_uuid = $project->uuid;
                $duplicate->save();
            });

            Hse::select('uuid', 'title')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection('transaction');
                $duplicate->setTable('hses');
                $duplicate->project_uuid = $project->uuid;
                $duplicate->save();
            });

            // TODO Additional Scope
            AdditionalScope::select('uuid', 'name', 'category', 'day', 'animation')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($additionalScope) use ($project) {
                $duplicateAdditionalScope = $additionalScope->replicate();
                $duplicateAdditionalScope->setConnection('transaction');
                $duplicateAdditionalScope->setTable('additional_scopes');
                $duplicateAdditionalScope->project_uuid = $project->uuid;
                $duplicateAdditionalScope->save();

                // TODO sequence animation
                SequenceAnimation::select('name', 'slug')->where('additional_scope_uuid', $additionalScope->uuid)->each(function ($row) use ($duplicateAdditionalScope) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('sequence_animations');
                    $duplicate->additional_scope_uuid = $duplicateAdditionalScope->uuid;
                    $duplicate->save();
                });

                // tools
                Tools::select('name', 'qty', 'global_unit_uuid', 'additional_scope_uuid', 'section')->where('additional_scope_uuid', $additionalScope->uuid)->each(function ($row) use ($project, $duplicateAdditionalScope) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('tools');
                    $duplicate->additional_scope_uuid = $duplicateAdditionalScope->uuid;
                    $duplicate->save();
                });

                // TODO part
                Part::select('name', 'qty', 'global_unit_uuid', 'size', 'no_drawing', 'location')->where('additional_scope_uuid', $additionalScope->uuid)->each(function ($row) use ($project, $duplicateAdditionalScope) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('parts');
                    $duplicate->additional_scope_uuid = $duplicateAdditionalScope->uuid;
                    $duplicate->save();
                });

                Sequence::select('uuid', 'name', 'link')->where('additional_scope_uuid', $additionalScope->uuid)->each(function ($row) use ($project, $duplicateAdditionalScope) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('sequences');
                    $duplicate->additional_scope_uuid = $duplicateAdditionalScope->uuid;
                    $duplicate->save();
                });

                ScopeStandart::select('uuid', 'name', 'link', 'category')->where('additional_scope_uuid', $additionalScope->uuid)->each(function ($row) use ($project, $duplicateAdditionalScope) {
                    $scopeStandart = $row->replicate();
                    $scopeStandart->setConnection('transaction');
                    $scopeStandart->setTable('scope_standarts');
                    $scopeStandart->additional_scope_uuid = $duplicateAdditionalScope->uuid;
                    $scopeStandart->save();

                    DetailScopeStandart::select('uuid', 'name', 'link')->where('scope_standart_uuid', $row->uuid)->each(function ($row) use ($scopeStandart) {
                        $duplicate = $row->replicate();
                        $duplicate->setConnection('transaction');
                        $duplicate->setTable('detail_scope_standarts');
                        $duplicate->scope_standart_uuid = $scopeStandart->uuid;
                        $duplicate->save();
                    });
                });

                Manpower::select('uuid', 'name', 'qty', 'type')->where('additional_scope_uuid', $additionalScope->uuid)->each(function ($row) use ($project, $duplicateAdditionalScope) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('manpowers');
                    $duplicate->additional_scope_uuid = $duplicateAdditionalScope->uuid;
                    $duplicate->save();
                });

                ConsMat::select('uuid', 'name', 'merk', 'qty', 'global_unit_uuid')->where('additional_scope_uuid', $additionalScope->uuid)->each(function ($row) use ($project, $duplicateAdditionalScope) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('const_mats');
                    $duplicate->additional_scope_uuid = $duplicateAdditionalScope->uuid;
                    $duplicate->save();
                });

                QcPlan::insert([
                    [
                        'uuid' => Str::uuid(),
                        'name' => 'QC Plan Mekanik',
                        'additional_scope_uuid' => $duplicateAdditionalScope->uuid
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'name' => 'QC Plan Listrik',
                        'additional_scope_uuid' => $duplicateAdditionalScope->uuid
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'name' => 'QC Plan Instrument',
                        'additional_scope_uuid' => $duplicateAdditionalScope->uuid
                    ]
                ]);
            });

            QcPlan::insert([
                [
                    'uuid' => Str::uuid(),
                    'name' => 'QC Plan Mekanik',
                    'project_uuid' => $project->uuid
                ],
                [
                    'uuid' => Str::uuid(),
                    'name' => 'QC Plan Listrik',
                    'project_uuid' => $project->uuid
                ],
                [
                    'uuid' => Str::uuid(),
                    'name' => 'QC Plan Instrument',
                    'project_uuid' => $project->uuid
                ]
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return $project;
    }
}
