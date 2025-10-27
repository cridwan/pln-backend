<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
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
            'subBidang.bidang'
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
            new AttributeData('name', 'NAME'),
            new AttributeData(function ($row) {
                return $row->subBidang?->name ?? '';
            }, 'SUB BIDANG'),
            new AttributeData(function ($row) {
                return $row->subBidang?->bidang?->name ?? '';
            }, 'BIDANG'),
            new AttributeData(function ($row) {
                return $row->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData('created_at', 'CREATED AT'),
            new AttributeData('updated_at', 'UPDATED AT'),
        ];
    }

    #[DoNotDiscover]
    public function attributeTemplate(): TemplateData
    {
        return new TemplateData([
            'name',
            'inspection_type_uuid',
            'sub_bidang_uuid'
        ], [
            new OptionData(
                'B',
                InspectionType::pluck('name', 'uuid')
                    ->map(fn($name, $uuid) => "$name / $uuid")
                    ->values()
                    ->toArray()
            ),
            new OptionData(
                'C',
                SubBidang::pluck('name', 'uuid')
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
            ScopeStandart::create([
                'name' => $data['name'],
                'inspection_type_uuid' => trim(str($data['inspection_type_uuid'])->explode('/')->toArray()[1]),
                'sub_bidang_uuid' => trim(str($data['sub_bidang_uuid'])->explode('/')->toArray()[1]),
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
