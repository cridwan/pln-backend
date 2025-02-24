<?php

namespace App\Services;

use App\Http\Requests\GenerateRequest;
use App\Models\AdditionalScope;
use App\Models\ConstMat;
use App\Models\DetailScopeStandart;
use App\Models\Hse;
use App\Models\Manpower;
use App\Models\Part;
use App\Models\ScopeStandart;
use App\Models\Sequence;
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

            Part::select('name', 'qty', 'global_unit_uuid')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection('transaction');
                $duplicate->setTable('parts');
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
                $duplicate = $row->replicate();
                $duplicate->setConnection('transaction');
                $duplicate->setTable('scope_standarts');
                $duplicate->project_uuid = $project->uuid;
                $duplicate->save();

                DetailScopeStandart::select('uuid', 'name', 'link', 'scope_standart_uuid')->where('scope_standart_uuid', $duplicate->uuid)->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('detail_scope_standarts');
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

            ConstMat::select('uuid', 'name', 'merk', 'qty', 'global_unit_uuid')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
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
            AdditionalScope::select('uuid', 'name', 'category')->where('inspection_type_uuid', $request->inspection_type_uuid)->each(function ($row) use ($project) {
                $duplicate = $row->replicate();
                $duplicate->setConnection('transaction');
                $duplicate->setTable('additional_scopes');
                $duplicate->project_uuid = $project->uuid;
                $duplicate->save();

                // TODO part
                Part::select('name', 'qty', 'global_unit_uuid', 'additional_scope_uuid')->where('additional_scope_uuid', $row->uuid)->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('parts');
                    $duplicate->save();
                });

                Sequence::select('uuid', 'name', 'link', 'additional_scope_uuid')->where('additional_scope_uuid', $row->uuid)->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('sequences');
                    $duplicate->save();
                });

                ScopeStandart::select('uuid', 'name', 'link', 'category', 'additional_scope_uuid')->where('additional_scope_uuid', $row->uuid)->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('scope_standarts');
                    $duplicate->save();

                    DetailScopeStandart::select('uuid', 'name', 'link', 'scope_standart_uuid')->where('scope_standart_uuid', $duplicate->uuid)->each(function ($row) use ($project) {
                        $duplicate = $row->replicate();
                        $duplicate->setConnection('transaction');
                        $duplicate->setTable('detail_scope_standarts');
                        $duplicate->save();
                    });
                });

                Manpower::select('uuid', 'name', 'qty', 'type', 'additional_scope_uuid')->where('additional_scope_uuid', $row->uuid)->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('manpowers');
                    $duplicate->save();
                });

                ConstMat::select('uuid', 'name', 'merk', 'qty', 'global_unit_uuid', 'additional_scope_uuid')->where('additional_scope_uuid', $row->uuid)->each(function ($row) use ($project) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection('transaction');
                    $duplicate->setTable('const_mats');
                    $duplicate->save();
                });

                QcPlan::insert([
                    [
                        'uuid' => Str::uuid(),
                        'name' => 'Qc Plan Mekanik',
                        'additional_scope_uuid' => $duplicate->uuid
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'name' => 'Qc Plan Listrik',
                        'additional_scope_uuid' => $duplicate->uuid
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'name' => 'Qc Plan Instrument',
                        'additional_scope_uuid' => $duplicate->uuid
                    ]
                ]);
            });

            QcPlan::insert([
                [
                    'uuid' => Str::uuid(),
                    'name' => 'Qc Plan Mekanik',
                    'project_uuid' => $project->uuid
                ],
                [
                    'uuid' => Str::uuid(),
                    'name' => 'Qc Plan Listrik',
                    'project_uuid' => $project->uuid
                ],
                [
                    'uuid' => Str::uuid(),
                    'name' => 'Qc Plan Instrument',
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
