<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Activity;
use App\Models\Manpower;
use App\Models\ManpowerStd;
use App\Models\Part;
use App\Models\PartStd;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class ManpowerStdCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'manpower',
            'activity',
            'activity.equipment',
            'activity.equipment.scopeStandart',
            'activity.equipment.scopeStandart.inspectionType',
            'activity.equipment.scopeStandart.inspectionType.machine',
            'activity.equipment.scopeStandart.inspectionType.machine.unit',
            'activity.equipment.scopeStandart.inspectionType.machine.unit.location',
            'activity.equipment.scopeStandart.subBidang',
            'activity.equipment.scopeStandart.subBidang.bidang'
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'manpower.name',
            'asc'
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return ManpowerStd::class;
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'activity_uuid' => 'required|exists:activities,uuid',
            'manpower_uuid' => 'required|exists:manpowers,uuid',
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
                return $row->manpower?->name ?? '';
            }, 'MANPOWER'),
            new AttributeData(function ($row) {
                return $row->qty;
            }, 'QTY'),
            new AttributeData(function ($row) {
                return 'Rp ' . number_format($row->manpower?->price, 2);
            }, 'PRICE'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->subBidang?->name ?? '';
            }, 'SUB BIDANG'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->subBidang?->bidang?->name ?? '';
            }, 'BIDANG'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->name ?? '';
            }, 'SCOPE STANDART'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->name ?? '';
            }, 'EQUIPMENT'),
            new AttributeData(function ($row) {
                return $row->activity?->name ?? '';
            }, 'EQUIPMENT'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData('created_at', 'CREATED AT'),
            new AttributeData('updated_at', 'UPDATED AT'),
        ];
    }

    #[DoNotDiscover]
    public function attributeTemplate(): TemplateData
    {
        return new TemplateData([
            'qty',
            'activity_uuid',
            'manpower_uuid',
        ], [
            new OptionData(
                'B',
                Activity::pluck('name', 'uuid')
                    ->map(fn($name, $uuid) => "$name / $uuid")
                    ->values()
                    ->toArray()
            ),
            new OptionData(
                'C',
                Manpower::pluck('name', 'uuid')
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
            ManpowerStd::create([
                'qty' => $data['qty'],
                'activity_uuid' => trim(str($data['activity_uuid'])->explode('/')->toArray()[1]),
                'manpower_uuid' => trim(str($data['manpower_uuid'])->explode('/')->toArray()[1]),
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
