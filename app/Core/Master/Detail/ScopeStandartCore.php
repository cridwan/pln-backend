<?php

namespace App\Core\Master\Detail;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\AdditionalScope;
use App\Models\InspectionType;
use App\Models\ScopeStandart;
use App\Models\SubBidang;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class ScopeStandartCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'inspectionType',
            'inspectionType.machine',
            'inspectionType.machine.unit',
            'inspectionType.machine.unit.location',
            'subBidang',
            'subBidang.bidang',
            'activityLog.createdBy',
            'activityLog.updatedBy',
            'document',
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
        return ScopeStandart::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return ScopeStandart::query()
            ->has('additionalScope')
            ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('additionalScope.inspectionType.machine.unit.location.subArea', function ($q) {
                    $q->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            });
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            "name" => "required",
            "link" => "nullable",
            "additional_scope_uuid" => "nullable|exists:additional_scopes,uuid",
            "inspection_type_uuid" => "nullable|exists:inspection_types,uuid",
            "sub_bidang_uuid" => "required|exists:sub_bidangs,uuid",
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
                return $row->additionalScope?->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData(function ($row) {
                return $row->additionalScope?->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->additionalScope?->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->additionalScope?->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->additionalScope?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->subBidang?->bidang?->name ?? '';
            }, 'BIDANG'),
            new AttributeData(function ($row) {
                return $row->subBidang?->name ?? '';
            }, 'SUB BIDANG'),
            new AttributeData('name', 'NAME'),
            new AttributeData('created_at', 'CREATED AT'),
            new AttributeData('updated_at', 'UPDATED AT'),
            new AttributeData(function ($row) {
                return $row->activityLog?->createdBy?->name ?? '';
            }, 'CREATED BY'),
            new AttributeData(function ($row) {
                return $row->activityLog?->updatedBy?->name ?? '';
            }, 'UPDATED BY'),
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
        $additionalScope = request()->collect('filters')->where('column', '=', 'additional_scope_uuid')->first();
        return new TemplateData([
            'additional_scope_uuid',
            'sub_bidang_uuid',
            'name',
        ], [
            new OptionData(
                'A',
                AdditionalScope::
                    when(auth()->user() && !auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($where) {
                        $where->whereHas('inspectionType.machine.unit.location.subArea', fn($query) => $query->where('area_uuid', '=', auth()->user()->area_uuid));
                    })
                    ->when($additionalScope, function ($query) use ($additionalScope) {
                        $query->where('uuid', '=', $additionalScope['value'] ?? null);
                    })
                    ->with(['inspectionType.machine.unit.location'])
                    ->get()
                    ->map(function ($row) {
                        $machine = $row->inspectionType?->machine?->name;
                        $unit = $row->inspectionType?->machine?->unit?->name;
                        $location = $row->inspectionType?->machine?->unit?->location?->name;
                        $inspection = $row->inspectionType?->name ?? '';
                        return "$location / $unit/ $machine / $inspection / $row->name / $row->uuid";
                    })
                    ->values()
                    ->toArray()
            ),
            new OptionData(
                'B',
                SubBidang::with(['bidang'])
                    ->get()
                    ->map(function ($row) {
                        $bidang = $row->bidang?->name;
                        return "$bidang / $row->name / $row->uuid";
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
            $inspectionType = str($data['additional_scope_uuid'] ?? '')->explode('/')->toArray();
            $subBidang = str($data['sub_bidang_uuid'] ?? '')->explode('/')->toArray();
            ScopeStandart::create([
                'name' => $data['name'] ?? '',
                'additional_scope_uuid' => trim(end($inspectionType)),
                'sub_bidang_uuid' => trim(end($subBidang)),
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
