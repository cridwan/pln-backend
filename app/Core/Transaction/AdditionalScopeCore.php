<?php

namespace App\Core\Transaction;

use App\Core\TransactionCore;
use App\Data\AttributeData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Transaction\AdditionalScope;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class AdditionalScopeCore extends TransactionCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'assetWelnes.document',
            'ohRecom.document',
            'woPriority.document',
            'history.document',
            'rla.document',
            'ncr.document'
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'created_at',
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
        return [];
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
            'sequence_uuid',
        ], []);
    }

    #[DoNotDiscover]
    public function mapping($data, $index)
    {
        $data = $this->validateData($data, $index);

        if (empty($data) || count($data) == 0) {
            return null;
        }

        try {
            AdditionalScope::create([
                'name' => $data['name'],
                'inspection_type_uuid' => trim(str($data['inspection_type_uuid'])->explode('/')->toArray()[1]),
                'sequence_uuid' => trim(str($data['sequence_uuid'])->explode('/')->toArray()[1]),
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
