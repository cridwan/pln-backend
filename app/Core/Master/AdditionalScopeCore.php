<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\AdditionalScope;
use App\Models\InspectionType;
use App\Models\Sequence;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class AdditionalScopeCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'inspectionType.machine.unit.location',
            'sequence',
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
        return AdditionalScope::class;
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'name' => 'required',
            'inspection_type_uuid' => 'required|exists:inspection_types,uuid',
            'sequence_uuid' => 'nullable|exists:sequences,uuid',
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
                return $row->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData(function ($row) {
                return $row->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
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
            'inspection_type_uuid',
            'sequence_uuid',
            'name',
        ], [
            new OptionData(
                'A',
                InspectionType::
                    when(auth()->user() && !auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($where) {
                        $where->whereHas('machine.unit.location.subArea', fn($query) => $query->where('area_uuid', '=', auth()->user()->area_uuid));
                    })
                    ->with(['machine.unit.location'])
                    ->get()
                    ->map(function ($row) {
                        $machine = $row->machine?->name;
                        $unit = $row->machine?->unit?->name;
                        $location = $row->machine?->unit?->location?->name;
                        return "$machine / $unit / $location / $row->name / $row->uuid";
                    })
                    ->values()
                    ->toArray()
            ),
            new OptionData(
                'B',
                Sequence::pluck('name', 'uuid')
                    ->map(fn($name, $uuid) => "$name / $uuid")
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
            $inspection = str($data['inspection_type_uuid'] ?? '')->explode('/')->toArray();
            $sequence = str($data['sequence_uuid'] ?? '')->explode('/')->toArray();
            AdditionalScope::create([
                'name' => $data['name'] ?? null,
                'inspection_type_uuid' => trim(end($inspection)),
                'sequence_uuid' => trim(end($sequence)),
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
