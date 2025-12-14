<?php

namespace App\Services;

use App\Data\WhereOptionData;
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
use App\Models\Storage\Document;
use App\Models\ToolsStd;
use App\Models\Transaction\ConsMat;
use App\Models\Transaction\Manpower;
use App\Models\Transaction\Part;
use App\Models\Transaction\Project;
use App\Models\QcPlan;
use App\Models\Transaction\Tools;
use Exception;
use Illuminate\Support\Facades\DB;

class GenerateService
{
    private int $chunkSize = 50;
    public static function make()
    {
        return new self;
    }

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
            $this->cloneHseDoc($project);

            // duplicate scope standart
            $this->cloneScopeStandart(new WhereOptionData(
                'inspection_type_uuid',
                '=',
                $request->inspection_type_uuid,
                [
                    'project_uuid' => $project->uuid
                ]
            ));

            // duplicate qc plan
            $this->cloneQcPln($project);

            // duplicate additional scope
            $this->cloneAdditionalScope(new WhereOptionData(
                'inspection_type_uuid',
                '=',
                $request->inspection_type_uuid,
                [
                    'project_uuid' => $project->uuid
                ]
            ));

            return $project;
        });

        return $generated;
    }

    public function cloneDocument(string $documentType, string $documentUuid, string $newDocumentType, string $newDocumentUuid)
    {
        Document::where('document_type', '=', $documentType)
            ->where('document_uuid', '=', $documentUuid)
            ->chunk($this->chunkSize, function ($documents) use ($newDocumentUuid, $newDocumentType) {
                foreach ($documents as $document) {
                    $document->create(
                        collect($document->toArray())
                            ->merge([
                                'document_uuid' => $newDocumentUuid,
                                'document_type' => $newDocumentType
                            ])
                            ->except('uuid', 'created_at', 'updated_at')
                            ->toArray()
                    );
                }
            });
    }

    public function cloneHseDoc(Project $project)
    {
        HseDoc::chunk($this->chunkSize, function ($rows) use ($project) {
            foreach ($rows as $row) {
                \App\Models\Transaction\HseDoc::create([
                    'name' => $row->name,
                    'project_uuid' => $project->uuid,
                    'original_uuid' => $row->uuid
                ]);
            }
        });
    }

    public function cloneHseDocSpesific(WhereOptionData $option)
    {
        HseDoc::
            where($option->column, $option->operator, $option->value)
            ->chunk($this->chunkSize, function ($rows) use ($option) {
                foreach ($rows as $row) {
                    \App\Models\Transaction\HseDoc::create(array_merge([
                        'name' => $row->name,
                        'original_uuid' => $row->uuid
                    ], $option->data));
                }
            });
    }

    public function cloneQcPln(Project $project)
    {
        QcPlan::chunk($this->chunkSize, function ($rows) use ($project) {
            foreach ($rows as $row) {
                \App\Models\Transaction\QcPlan::create([
                    'name' => $row->name,
                    'project_uuid' => $project->uuid,
                    'original_uuid' => $row->uuid
                ]);
            }
        });
    }

    public function cloneQcPlnSpesific(WhereOptionData $option)
    {
        QcPlan::
            where($option->column, $option->operator, $option->value)
            ->chunk($this->chunkSize, function ($rows) use ($option) {
                foreach ($rows as $row) {
                    \App\Models\Transaction\QcPlan::create(array_merge([
                        'name' => $row->name,
                        'original_uuid' => $row->uuid
                    ], $option->data));
                }
            });
    }

    public function cloneScopeStandart(WhereOptionData $option)
    {
        ScopeStandart::where($option->column, $option->operator, $option->value, $option->boolean)
            ->chunk($this->chunkSize, function ($scopes) use ($option) {
                foreach ($scopes as $scope) {
                    $duplicateScope = \App\Models\Transaction\ScopeStandart::create(array_merge([
                        'name' => $scope->name,
                        'link' => $scope->link,
                        'sub_bidang_uuid' => $scope->sub_bidang_uuid,
                        'original_uuid' => $scope->uuid,
                    ], $option->data));

                    // clone document
                    $this->cloneDocument(ScopeStandart::class, $scope->uuid, "App\\Models\\Transaction\\ScopeStandart", $duplicateScope->uuid);

                    // duplicate equipment
                    $this->cloneEquipment(new WhereOptionData(
                        'scope_standart_uuid',
                        '=',
                        $scope->uuid,
                        [
                            'scope_standart_uuid' => $duplicateScope->uuid
                        ]
                    ));
                }
            });
    }

    public function cloneAdditionalScope(WhereOptionData $option)
    {
        AdditionalScope::where($option->column, $option->operator, $option->value)
            ->chunk($this->chunkSize, function ($addScopes) use ($option) {
                foreach ($addScopes as $addScope) {
                    $duplicateAdScope = \App\Models\Transaction\AdditionalScope::create(array_merge([
                        'name' => $addScope->name,
                        'sequence_uuid' => $addScope->sequence_uuid,
                        'original_uuid' => $addScope->uuid,
                    ], $option->data));

                    // clone document
                    $this->cloneDocument(AdditionalScope::class, $addScope->uuid, "App\\Models\\Transaction\\AdditionalScope", $duplicateAdScope->uuid);

                    // duplicate scope standart
                    $this->cloneScopeStandart(new WhereOptionData(
                        'additional_scope_uuid',
                        '=',
                        $addScope->uuid,
                        [
                            'additional_scope_uuid' => $duplicateAdScope->uuid
                        ]
                    ));
                }
            });
    }

    public function cloneEquipment(WhereOptionData $option)
    {
        Equipment::where($option->column, $option->operator, $option->value, $option->boolean)
            ->chunk($this->chunkSize, function ($equipments) use ($option) {
                foreach ($equipments as $equipment) {
                    $duplicateEquipment = \App\Models\Transaction\Equipment::create(array_merge(
                        [
                            'name' => $equipment->name,
                            'link_1' => $equipment->link_1,
                            'link_2' => $equipment->link_2,
                            'original_uuid' => $equipment->uuid,
                        ],
                        $option->data
                    ));

                    // duplicate activity
                    $this->cloneActivity(new WhereOptionData(
                        'equipment_uuid',
                        '=',
                        $equipment->uuid,
                        [
                            'equipment_uuid' => $duplicateEquipment->uuid
                        ]
                    ));
                }
            });
    }

    public function cloneActivity(WhereOptionData $option)
    {
        Activity::where($option->column, $option->operator, $option->value)
            ->chunk($this->chunkSize, function ($activities) use ($option) {
                foreach ($activities as $activity) {
                    $duplicateActivity = \App\Models\Transaction\Activity::create(array_merge([
                        'name' => $activity->name,
                        'duration' => $activity->duration,
                        'serial_number' => $activity->serial_number,
                        'link_1' => $activity->link_1,
                        'link_2' => $activity->link_2,
                        'original_uuid' => $activity->uuid,
                    ], $option->data));

                    // clone document
                    $this->cloneDocument(Activity::class, $activity->uuid, "App\\Models\\Transaction\\Activity", $duplicateActivity->uuid);

                    // duplicate consumable material
                    $this->cloneConsumableMaterial(new WhereOptionData(
                        'activity_uuid',
                        '=',
                        $activity->uuid,
                        [
                            'activity_uuid' => $duplicateActivity->uuid
                        ]
                    ));

                    // duplicate tools std
                    $this->cloneTools(new WhereOptionData(
                        'activity_uuid',
                        '=',
                        $activity->uuid,
                        [
                            'activity_uuid' => $duplicateActivity->uuid
                        ]
                    ));

                    // duplicate part std
                    $this->clonePart(new WhereOptionData(
                        'activity_uuid',
                        '=',
                        $activity->uuid,
                        [
                            'activity_uuid' => $duplicateActivity->uuid
                        ]
                    ));

                    // duplicate manpower std
                    $this->cloneManpower(new WhereOptionData(
                        'activity_uuid',
                        '=',
                        $activity->uuid,
                        [
                            'activity_uuid' => $duplicateActivity->uuid
                        ]
                    ));
                }
            });
    }

    public function cloneConsumableMaterial(WhereOptionData $option)
    {
        ConsMatStd::with(['consmat.globalUnit'])
            ->where($option->column, $option->operator, $option->value)
            ->chunk($this->chunkSize, function ($rows) use ($option) {
                foreach ($rows as $row) {
                    ConsMat::create(array_merge([
                        'name' => $row->consmat?->name,
                        'merk' => $row->consmat?->name,
                        'unit' => $row->consmat?->globalUnit?->name,
                        'price' => $row->consmat?->price,
                        'qty' => $row->qty,
                        'original_uuid' => $row->uuid,
                    ], $option->data));
                }
            });
    }

    public function clonePart(WhereOptionData $option)
    {
        PartStd::with(['part.globalUnit'])
            ->where($option->column, $option->operator, $option->value)
            ->chunk($this->chunkSize, function ($rows) use ($option) {
                foreach ($rows as $row) {
                    Part::create(array_merge([
                        'name' => $row->part?->name,
                        'merk' => $row->part?->merk,
                        'no_drawing' => $row->part?->no_drawing,
                        'unit' => $row->part?->globalUnit?->name,
                        'price' => $row->part?->price,
                        'qty' => $row->qty,
                        'original_uuid' => $row->uuid,
                    ], $option->data));
                }
            });
    }


    public function cloneTools(WhereOptionData $option)
    {
        ToolsStd::with(['tool.globalUnit'])
            ->where($option->column, $option->operator, $option->value)
            ->chunk($this->chunkSize, function ($rows) use ($option) {
                foreach ($rows as $row) {
                    Tools::create(array_merge([
                        'name' => $row->tool?->name,
                        'merk' => $row->tool?->merk,
                        'unit' => $row->tool?->globalUnit?->name,
                        'price' => $row->tool?->price,
                        'status' => $row->tool?->status,
                        'qty' => $row->qty,
                        'original_uuid' => $row->uuid,
                    ], $option->data));
                }
            });
    }

    public function cloneManpower(WhereOptionData $option)
    {
        ManpowerStd::with(['manpower'])
            ->where($option->column, $option->operator, $option->value)
            ->chunk($this->chunkSize, function ($rows) use ($option) {
                foreach ($rows as $row) {
                    Manpower::create(array_merge([
                        'name' => $row->manpower?->name,
                        'price' => $row->manpower?->price,
                        'qty' => $row->qty,
                        'original_uuid' => $row->uuid,
                    ], $option->data));
                }
            });
    }
}
