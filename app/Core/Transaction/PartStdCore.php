<?php

namespace App\Core\Transaction;

use App\Core\TransactionCore;
use App\Data\AttributeData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Transaction\Part;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class PartStdCore extends TransactionCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'part',
            'part.globalUnit'
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'part.name',
            'asc'
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return Part::class;
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'name' => 'required',
            'qty' => 'required',
            'noDrawing' => 'nullable',
            'note' => 'nullable',
            'global_unit_uuid' => ['required', Rule::exists('masterdata.global_units', 'uuid')],
            'project_uuid' => ['nullable', Rule::exists('transaction.projects', 'uuid')],
            'additional_scope_uuid' => 'nullable'
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
                return $row->part?->name ?? '';
            }, 'PART'),
            new AttributeData(function ($row) {
                return $row->part?->globalUnit?->name ?? '';
            }, 'GLOBAL UNIT'),
            new AttributeData(function ($row) {
                return $row->qty;
            }, 'QTY'),
            new AttributeData(function ($row) {
                return 'Rp ' . number_format($row->part?->price, 2);
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
            'part_uuid',
        ], [
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
            Part::create([
                'qty' => $data['qty'],
                'activity_uuid' => trim(str($data['activity_uuid'])->explode('/')->toArray()[1]),
                'part_uuid' => trim(str($data['part_uuid'])->explode('/')->toArray()[1]),
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
