<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Location;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class UnitCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'location',
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
        return Unit::class;
    }

    #[DoNotDiscover]
    public function query(): Builder
    {
        return Unit::query()
            ->when(!auth()->user()->hasAnyRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('location.subArea', function ($q) {
                    $q->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            });
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'location_uuid' => ['required', Rule::exists('masterdata.locations', 'uuid')]
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
                return $row->location?->name ?? '';
            }, 'LOCATION'),
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
            'location_uuid',
            'name',
        ], new OptionData(
            column: 'A',
            options: Location::
                when(!auth()->user()->hasAnyRole(RoleEnum::SUPERUSER), function ($query) {
                    $query->whereHas('subArea', function ($q) {
                        $q->where('area_uuid', '=', auth()->user()->area_uuid);
                    });
                })
                ->pluck('name', 'uuid')
                ->map(fn($name, $uuid) => "$name / $uuid")
                ->values()
                ->toArray(),
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
            Unit::create([
                'name' => $data['name'] ?? '',
                'location_uuid' => trim(str($data['location_uuid'])->explode('/')->toArray()[1]),
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
