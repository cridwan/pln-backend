<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Equipment;
use App\Models\ScopeStandart;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class EquipmentCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'scopeStandart',
            'scopeStandart.inspectionType',
            'scopeStandart.inspectionType.machine',
            'scopeStandart.inspectionType.machine.unit',
            'scopeStandart.inspectionType.machine.unit.location',
            'scopeStandart.subBidang',
            'scopeStandart.subBidang.bidang',
            'activityLog.updatedBy',
            'activityLog.createdBy',
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'name',
            'asc',
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return Equipment::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return Equipment::query()
            ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('scopeStandart.inspectionType.machine.unit.location.subArea', function ($where) {
                    $where->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            })
            ->hasTransaction();
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'name' => 'required',
            'scope_standart_uuid' => 'required|exists:scope_standarts,uuid',
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
                return $row->scopeStandart?->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->subBidang?->bidang?->name ?? '';
            }, 'BIDANG'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->subBidang?->name ?? '';
            }, 'SUB BIDANG'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->name ?? '';
            }, 'SCOPE STANDART'),
            new AttributeData('name', 'NAME'),
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
            'scope_standart_uuid',
            'name',
        ], new OptionData(
            'A',
            ScopeStandart::
                when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                    $query->whereHas('inspectionType.machine.unit.location.subArea', function ($where) {
                        $where->where('area_uuid', '=', auth()->user()->area_uuid);
                    });
                })
                ->with(['inspectionType.machine.unit.location', 'subBidang'])
                ->get()
                ->map(function ($row) {
                    $inspectionType = $row->inspectionType?->name ?? '';
                    $machine = $row->inspectionType?->machine?->name ?? '';
                    $unit = $row->inspectionType?->machine->unit?->name ?? '';
                    $location = $row->inspectionType?->machine?->unit?->location?->name ?? '';
                    $subBidang = $row->subBidang?->name ?? '';
                    return "$location / $unit / $machine/ $inspectionType / $subBidang / $row->name / $row->uuid";
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
            $scopeStandart = str($data['scope_standart_uuid'] ?? '')->explode('/')->toArray();
            Equipment::create([
                'name' => $data['name'] ?? '',
                'scope_standart_uuid' => trim(end($scopeStandart)),
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
