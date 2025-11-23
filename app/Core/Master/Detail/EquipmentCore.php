<?php

namespace App\Core\Master\Detail;

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
            ->has('scopeStandart.additionalScope')
            ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('scopeStandart.additionalScope.inspectionType.machine.unit.location.subArea', function ($where) {
                    $where->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            });
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
                return $row->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->additionalScope?->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->additionalScope?->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->additionalScope?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->additionalScope?->subBidang?->bidang?->name ?? '';
            }, 'BIDANG'),
            new AttributeData(function ($row) {
                return $row->scopeStandart?->additionalScope?->subBidang?->name ?? '';
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
        $additionalScope = request()->collect('filters')->where('column', '=', 'scopeStandart.additional_scope_uuid')->first();
        return new TemplateData([
            'scope_standart_uuid',
            'name',
        ], new OptionData(
            'A',
            ScopeStandart::
                has('additionalScope')
                ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                    $query->whereHas('additionalScope.inspectionType.machine.unit.location.subArea', function ($where) {
                        $where->where('area_uuid', '=', auth()->user()->area_uuid);
                    });
                })
                ->when($additionalScope, function ($query) use ($additionalScope) {
                    $query->where('additional_scope_uuid', '=', $additionalScope['value']);
                })
                ->with(['additionalScope.inspectionType.machine.unit.location', 'subBidang'])
                ->get()
                ->map(function ($row) {
                    $additional = $row->additionalScope?->name ?? '';
                    $inspectionType = $row->additionalScope?->inspectionType?->name ?? '';
                    $machine = $row->additionalScope?->inspectionType?->machine?->name ?? '';
                    $unit = $row->additionalScope?->inspectionType?->machine->unit?->name ?? '';
                    $location = $row->additionalScope?->inspectionType?->machine?->unit?->location?->name ?? '';
                    $subBidang = $row->subBidang?->name ?? '';
                    return "$location / $unit / $machine/ $inspectionType / $additional / $subBidang / $row->name / $row->uuid";
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
