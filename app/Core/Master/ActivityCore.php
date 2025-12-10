<?php

namespace App\Core\Master;

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
            ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('equipment.scopeStandart.inspectionType.machine.unit.location.subArea', function ($where) {
                    $where->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            })
            ->hasTransaction()
            ->orderBy('serial_number', 'asc');
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
            'serial_number' => 'required',
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
                return $row->equipment?->scopeStandart?->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->equipment?->scopeStandart?->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
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
            new AttributeData('duration', 'DURATION (Jam)'),
            new AttributeData('serial_number', 'No Urut'),
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
        return new TemplateData([
            'equipment_uuid',
            'name',
            'duration',
            'serial_number',
        ], new OptionData(
            'A',
            Equipment::
                has('scopeStandart.inspectionType')
                ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                    $query->whereHas('scopeStandart.inspectionType.machine.unit.location.subArea', function ($where) {
                        $where->where('area_uuid', '=', auth()->user()->area_uuid);
                    });
                })
                ->with(['scopeStandart.inspectionType.machine.unit.location', 'scopeStandart.subBidang'])
                ->get()
                ->map(function ($row) {
                    $scope = $row->scopeStandart?->name ?? '';
                    $inspectionType = $row->scopeStandart?->inspectionType?->name ?? '';
                    $machine = $row->scopeStandart?->inspectionType?->machine?->name ?? '';
                    $unit = $row->scopeStandart?->inspectionType?->machine->unit?->name ?? '';
                    $location = $row->scopeStandart?->inspectionType?->machine?->unit?->location?->name ?? '';
                    $subBidang = $row->scopeStandart?->subBidang?->name ?? '';
                    return "$location / $unit / $machine/ $inspectionType / $subBidang / $scope / $row->name / $row->uuid";
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
                'serial_number' => $data['serial_number'] ?? null,
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
