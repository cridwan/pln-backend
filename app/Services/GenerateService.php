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
use Exception;
use Illuminate\Support\Facades\DB;

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

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return $project;
    }
}
