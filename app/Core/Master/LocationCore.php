<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\GeneratorTypeEnum;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\GeneratorType;
use App\Models\Location;
use App\Models\SubArea;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class LocationCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'updatedBy',
            'subArea',
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
        return Location::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return Location::query()
            ->when(auth()->user() && !auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($where) {
                $where->whereHas('subArea', fn($query) => $query->where('area_uuid', '=', auth()->user()->area_uuid));
            });
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
            'sub_area_uuid' => ['required', Rule::exists(SubArea::class, 'uuid')],
            'generator_type_uuid' => ['required', Rule::exists(GeneratorType::class, 'uuid')],
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
            'kode',
            'description',
            'lat',
            'lon',
            'generator_type',
            'sub_area_uuid',
        ], [
            new OptionData(
                column: 'F',
                options: array_map(fn($case) => $case->name . ' / #' . $case->value, GeneratorTypeEnum::cases()),
            ),
            new OptionData(
                column: 'G',
                options: SubArea::
                    when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                        $query->where('area_uuid', '=', auth()->user()->area_uuid);
                    })
                    ->pluck('name', 'uuid')
                    ->map(fn($name, $uuid) => "$name / $uuid")
                    ->values()
                    ->toArray(),
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
            Location::create([
                'name' => $data['name'] ?? '',
                'slug' => $data['kode'] ?? '',
                'description' => $data['description'] ?? '',
                'lat' => $data['lat'] ?? '',
                'lon' => $data['lon'] ?? '',
                'color' => trim(str($data['generator_type'])->explode('/')->toArray()[1]),
                'sub_area_uuid' => trim(str($data['sub_area_uuid'])->explode('/')->toArray()[1])
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
