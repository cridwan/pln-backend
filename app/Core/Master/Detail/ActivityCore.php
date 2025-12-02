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
use App\Models\Equipment;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class ActivityCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [

            'document',
            'equipment',
            'equipment.scopeStandart',
            'equipment.scopeStandart.inspectionType',
            'equipment.scopeStandart.inspectionType.machine',
            'equipment.scopeStandart.inspectionType.machine.unit',
            'equipment.scopeStandart.inspectionType.machine.unit.location',
            'equipment.scopeStandart.subBidang',
            'equipment.scopeStandart.subBidang.bidang',
            'activityLog.updatedBy',
            'activityLog.createdBy',
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'serial_number',
            'asc',
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return Activity::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return Activity::query()
            ->has('equipment.scopeStandart.additionalScope')
            ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('equipment.scopeStandart.additionalScope.inspectionType.machine.unit.location.subArea', function ($where) {
                    $where->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            })
            ->hasTransactionDetail();
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'name' => 'required',
            'duration' => 'required',
            'equipment_uuid' => 'required|exists:equipment,uuid',
            'link_ik1' => 'nullable',
            'link_ik2' => 'nullable',
        ];
    }

    #[DoNotDiscover]
    public function search(): array
    {
        return [
            'name',
        ];
    }

    #[DoNotDiscover]
    public function attributeExport(): array
    {
        return [
            new AttributeData('uuid', 'UUID'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->additionalScope?->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->additionalScope?->name ?? '';
            }, 'ADDITIONAL SCOPE'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->subBidang?->bidang?->name ?? '';
            }, 'BIDANG'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->subBidang?->name ?? '';
            }, 'SUB BIDANG'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->name ?? '';
            }, 'SCOPE STANDART'),
            new AttributeData(function ($row) {
                return $row->equipment?->name ?? '';
            }, 'EQUIPMENT'),
            new AttributeData('name', 'NAME'),
            new AttributeData('duration', 'DURATION'),
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
        $additionalScope = request()->collect('filters')->where('column', '=', 'equipment.scopeStandart.additional_scope_uuid')->first();
        return new TemplateData([
            'equipment_uuid',
            'name',
            'duration',
        ], new OptionData(
            'A',
            Equipment::
                has('scopeStandart.additionalScope')
                ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                    $query->whereHas('scopeStandart.additionalScope.inspectionType.machine.unit.location.subArea', function ($where) {
                        $where->where('area_uuid', '=', auth()->user()->area_uuid);
                    });
                })
                ->when($additionalScope, function ($query) use ($additionalScope) {
                    $query->whereHas('scopeStandart', function ($where) use ($additionalScope) {
                        $where->where('additional_scope_uuid', '=', $additionalScope['value']);
                    });
                })
                ->with(['scopeStandart.additionalScope.inspectionType.machine.unit.location', 'scopeStandart.subBidang'])
                ->get()
                ->map(function ($row) {
                    $addScope = $row->scopeStandart?->additionalScope?->name ?? '';
                    $scope = $row->scopeStandart?->name ?? '';
                    $inspectionType = $row->scopeStandart?->additionalScope?->inspectionType?->name ?? '';
                    $machine = $row->scopeStandart?->additionalScope?->inspectionType?->machine?->name ?? '';
                    $unit = $row->scopeStandart?->additionalScope?->inspectionType?->machine->unit?->name ?? '';
                    $location = $row->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->location?->name ?? '';
                    $subBidang = $row->scopeStandart?->subBidang?->name ?? '';
                    return "$location / $unit / $machine/ $inspectionType / $addScope / $subBidang / $scope / $row->name / $row->uuid";
                })
                ->values()
                ->toArray()
        ));
    }

    #[DoNotDiscover]
    public function mapping($data, $index)
    {
        $data = $this->validateData($data, $index);

        if (empty($data) || count($data) == 0) {
            return null;
        }

        try {
            $equipment = str($data['equipment_uuid'] ?? '')->explode('/')->toArray();
            Activity::create([
                'name' => $data['name'] ?? '',
                'duration' => $data['duration'] ?? null,
                'equipment_uuid' => trim(end($equipment)),
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
