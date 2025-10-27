<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\GeneratorTypeEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Location;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class LocationCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'updatedBy'
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
        return Location::class;
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'name' => 'required',
            'slug' => 'required',
            'description' => 'nullable',
            'lat' => 'required',
            'lon' => 'required',
            'color' => 'required',
        ];
    }

    #[DoNotDiscover]
    public function search(): array
    {
        return [
            'name',
            'slug',
        ];
    }

    #[DoNotDiscover]
    public function attributeExport(): array
    {
        return [
            new AttributeData('uuid', 'UUID'),
            new AttributeData('name', 'NAME'),
            new AttributeData('slug', 'KODE'),
            new AttributeData('description', 'DESCRIPTION'),
            new AttributeData('lat', 'LAT'),
            new AttributeData('lon', 'LON'),
            new AttributeData('color', 'COLOR'),
            new AttributeData(function ($row) {
                $getType = GeneratorTypeEnum::getType($row->color)->name ?? '';
                return str($getType)->explode('_')->join('/');
            }, 'GENERATOR TYPE'),
            new AttributeData(function ($row) {
                return $row->updatedBy?->name ?? '';
            }, 'LAST UPDATED BY'),
            new AttributeData('created_at', 'CREATED AT'),
            new AttributeData('updated_at', 'UPDATED AT'),
        ];
    }

    #[DoNotDiscover]
    public function attributeTemplate(): TemplateData
    {
        return new TemplateData([
            'name',
            'slug',
            'description',
            'lat',
            'lon',
            'generator_type'
        ], new OptionData(
            column: 'F',
            options: array_map(fn($case) => $case->name . ' / #' . $case->value, GeneratorTypeEnum::cases()),
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
            Location::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'lat' => $data['lat'],
                'lon' => $data['lon'],
                'color' => trim(str($data['generator_type'])->explode('/')->toArray()[1]),
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
