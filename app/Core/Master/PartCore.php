<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\GlobalUnit;
use App\Models\Part;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class PartCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'globalUnit',
            'activityLog.createdBy',
            'activityLog.updatedBy',
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
        return Part::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        $activity = request()->collect('filters')->where('column', '=', 'activity_uuid')->first();
        return Part::
            query()
            ->doesntHaveStd($activity['value'] ?? null);
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            "name" => "required",
            "no_drawing" => "required",
            "global_unit_uuid" => "required",
            "price" => "required",
            "merk" => "required"
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
            new AttributeData('no_drawing', 'NO DRAWING'),
            new AttributeData('merk', 'MERK'),
            new AttributeData(function ($row) {
                return 'Rp ' . number_format($row->price, 2);
            }, 'PRICE'),
            new AttributeData(function ($row) {
                return $row->globalUnit?->name ?? '';
            }, 'GLOBAL UNIT'),
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
            'name',
            'merk',
            'no_drawing',
            'price',
            'global_unit_uuid'
        ], new OptionData(
            'E',
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
            Part::create([
                'name' => $data['name'] ?? '',
                'merk' => $data['merk'] ?? '',
                'no_drawing' => $data['no_drawing'] ?? '',
                'price' => $data['price'] ?? null,
                'global_unit_uuid' => trim(str($data['global_unit_uuid'] ?? '')->explode('/')->toArray()[1]),
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
