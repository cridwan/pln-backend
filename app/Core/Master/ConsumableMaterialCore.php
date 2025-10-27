<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\ConsMat;
use App\Models\GlobalUnit;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class ConsumableMaterialCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'globalUnit'
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
        return ConsMat::class;
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'name' => 'required',
            "merk" => "required",
            "price" => "required",
            "global_unit_uuid" => "required",
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
                return 'Rp ' . number_format($row->price, 2);
            }, 'PRICE'),
            new AttributeData(function ($row) {
                return $row->globalUnit?->name ?? '';
            }, 'GLOBAL UNIT'),
            new AttributeData('created_at', 'CREATED AT'),
            new AttributeData('updated_at', 'UPDATED AT'),
        ];
    }

    #[DoNotDiscover]
    public function attributeTemplate(): TemplateData
    {
        return new TemplateData([
            'name',
            'price',
            'global_unit_uuid'
        ], new OptionData(
            'C',
            GlobalUnit::pluck('name', 'uuid')
                ->map(fn($name, $uuid) => "$name / $uuid")
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
            ConsMat::create([
                'name' => $data['name'],
                'merk' => $data['merk'],
                'price' => $data['price'],
                'global_unit_uuid' => trim(str($data['unit_uuid'])->explode('/')->toArray()[1]),
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
