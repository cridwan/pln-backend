<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Machine;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class MachineCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'unit',
            'unit.location',
            'activityLog.createdBy',
            'activityLog.updatedBy',
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'name',
            'asc'
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return Machine::class;
    }

    #[DoNotDiscover]
    public function query(): Builder
    {
        return Machine::query()
            ->when(!auth()->user()->hasAnyRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('unit.location.subArea', function ($q) {
                    $q->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            });
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'unit_uuid' => ['required', Rule::exists('masterdata.units', 'uuid')]
        ];
    }

    #[DoNotDiscover]
    public function search(): array
    {
        return [
            'name',
            'unit.name'
        ];
    }

    #[DoNotDiscover]
    public function attributeExport(): array
    {
        return [
            new AttributeData('uuid', 'UUID'),
            new AttributeData('name', 'NAME'),
            new AttributeData(function ($row) {
                return $row->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->unit?->location?->name ?? '';
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
            'unit_uuid',
            'name',
        ], new OptionData(
            column: 'A',
            options: Unit::
                when(!auth()->user()->hasAnyRole(RoleEnum::SUPERUSER), function ($query) {
                    $query->whereHas('location.subArea', function ($q) {
                        $q->where('area_uuid', '=', auth()->user()->area_uuid);
                    });
                })
                ->with(['location'])
                ->get()
                ->map(function ($row) {
                    $location = $row->location?->name;
                    return "$location / $row->name / $row->uuid";
                })
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
            $unit_uuid = str($data['unit_uuid'])->explode('/')->toArray();
            Machine::create([
                'name' => $data['name'] ?? '',
                'unit_uuid' => trim(end($unit_uuid)),
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
