<?php

namespace App\Core\Master\Detail;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Activity;
use App\Models\ConsMat;
use App\Models\ConsMatStd;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class ConsumableMaterialStdCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'consmat.globalUnit',
            'activity',
            'activity.equipment',
            'activity.equipment.scopeStandart',
            'activity.equipment.scopeStandart.inspectionType',
            'activity.equipment.scopeStandart.inspectionType.machine',
            'activity.equipment.scopeStandart.inspectionType.machine.unit',
            'activity.equipment.scopeStandart.inspectionType.machine.unit.location',
            'activity.equipment.scopeStandart.subBidang',
            'activity.equipment.scopeStandart.subBidang.bidang',
            'activityLog.updatedBy',
            'activityLog.createdBy',
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'consmat.name',
            'asc'
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return ConsMatStd::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return ConsMatStd::query()
            ->has('activity.equipment.scopeStandart.additionalScope')
            ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('activity.equipment.scopeStandart.additionalScope.inspectionType.machine.unit.location.subArea', function ($where) {
                    $where->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            })
            ->hasTransactionDetail();
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'activity_uuid' => 'required|exists:activities,uuid',
            'cons_mat_uuid' => 'required|exists:const_mats,uuid',
            'qty' => 'required',
        ];
    }

    #[DoNotDiscover]
    public function search(): array
    {
        return [];
    }

    #[DoNotDiscover]
    public function attributeExport(): array
    {
        return [
            new AttributeData('uuid', 'UUID'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->additionalScope?->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->additionalScope?->name ?? '';
            }, 'ADDITIONAL SCOPE'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->subBidang?->bidang?->name ?? '';
            }, 'BIDANG'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->subBidang?->name ?? '';
            }, 'SUB BIDANG'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->name ?? '';
            }, 'SCOPE STANDART'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->name ?? '';
            }, 'EQUIPMENT'),
            new AttributeData(function ($row) {
                return $row->activity?->name ?? '';
            }, 'ACTIVITY'),
            new AttributeData(function ($row) {
                return $row->consmat?->name ?? '';
            }, 'CONSUMABLE MATERIAL'),
            new AttributeData(function ($row) {
                return $row->consmat?->globalUnit?->name ?? '';
            }, 'SATUAN'),
            new AttributeData(function ($row) {
                return $row->qty;
            }, 'QTY'),
            new AttributeData(function ($row) {
                return 'Rp ' . number_format($row->consmat?->price, 2);
            }, 'PRICE'),
            new AttributeData('created_at', 'CREATED AT'),
            new AttributeData('updated_at', 'UPDATED AT'),
            new AttributeData(function ($row) {
                return $row->activityLog?->createdBy?->name ?? '';
            }, 'CREATED BY'),
            new AttributeData(function ($row) {
                return $row->activityLog?->updatedBy?->name ?? '';
            }, 'UPDATED BY'),
        ];
    }

    #[DoNotDiscover]
    public function attributeTemplate(): TemplateData
    {
        $additionalScope = request()->collect('filters')->where('column', '=', 'activity.equipment.scopeStandart.additionalScope')->first();
        return new TemplateData([
            'activity_uuid',
            'cons_mat_uuid',
            'qty',
        ], [
            new OptionData(
                'A',
                Activity::
                    has('equipment.scopeStandart.additionalScope')
                    ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                        $query->whereHas('equipment.scopeStandart.additionalScope.inspectionType.machine.unit.location.subArea', function ($where) {
                            $where->where('area_uuid', '=', auth()->user()->area_uuid);
                        });
                    })
                    ->when($additionalScope, function ($query) use ($additionalScope) {
                        $query->whereHas('equipment.scopeStandart', function ($where) use ($additionalScope) {
                            $where->where('additional_scope_uuid', '=', $additionalScope['value']);
                        });
                    })
                    ->with(['equipment.scopeStandart.additionalScope.inspectionType.machine.unit.location', 'equipment.scopeStandart.subBidang'])
                    ->get()
                    ->map(function ($row) {
                        $equipment = $row->equipment?->name ?? '';
                        $scope = $row->equipment?->scopeStandart?->name ?? '';
                        $inspectionType = $row->equipment?->scopeStandart?->additionalScope?->inspectionType?->name ?? '';
                        $machine = $row->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->name ?? '';
                        $unit = $row->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine->unit?->name ?? '';
                        $location = $row->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->location?->name ?? '';
                        $subBidang = $row->equipment?->scopeStandart?->subBidang?->name ?? '';
                        $addScope = $row->equipment?->scopeStandart?->additionalScope?->name ?? '';
                        return "$location / $unit / $machine/ $inspectionType / $addScope / $subBidang / $scope / $equipment / $row->name / $row->uuid";
                    })
                    ->values()
                    ->toArray()
            ),
            new OptionData(
                'B',
                ConsMat::
                    with(['globalUnit'])
                    ->get()
                    ->map(function ($row) {
                        $unit = $row->globalUnit?->name ?? '';
                        return "$row->name - $unit / $row->uuid";
                    })
                    ->values()
                    ->toArray()
            )
        ]);
    }

    #[DoNotDiscover]
    public function mapping($data, $index)
    {
        $data = $this->validateData($data, $index);

        if (empty($data) || count($data) == 0) {
            return null;
        }

        try {
            $activity = str($data['activity_uuid'] ?? '')->explode('/')->toArray();
            $constmat = str($data['cons_mat_uuid'] ?? '')->explode('/')->toArray();
            ConsMatStd::create([
                'qty' => $data['qty'] ?? null,
                'activity_uuid' => trim(end($activity)),
                'cons_mat_uuid' => trim(end($constmat)),
            ]);
        } catch (\Throwable $th) {
            // Lempar error agar transaksi berhenti → rollback di controller
            throw $th;
        }
    }

    #[DoNotDiscover]
    public function validateData($data, $index): array
    {
        $map = [];
        foreach ($this->attributeTemplate()->headers as $header) {
            if ($index == 1) {
                if (!in_array($header, array_keys($data))) {
                    throw new BadRequestException("[Mapping]: Gagal mapping data. Pastikan anda menggunakan format excel yang sudah di sediakan");
                }
            }

            if (isset($data[$header]) && trim((string) $data[$header]) !== '') {
                $map[$header] = $data[$header];
            }
        }

        return $map;
    }
}
