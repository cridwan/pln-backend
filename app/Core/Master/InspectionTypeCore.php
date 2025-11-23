<?php

namespace App\Core\Master;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\InspectionType;
use App\Models\Machine;
use App\Models\Sequence;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class InspectionTypeCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'machine',
            'machine.unit',
            'machine.unit.location',
            'sequence',
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
        return InspectionType::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return InspectionType::query()
            ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('machine.unit.location.subArea', function ($q) {
                    $q->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            });
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'machine_uuid' => ['required', Rule::exists('masterdata.machines', 'uuid')],
            'sequence_uuid' => ['nullable', Rule::exists('sequences', 'uuid')]
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
            new AttributeData(function ($row) {
                return $row->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData(function ($row) {
                return $row->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData('name', 'NAME'),
            new AttributeData(function ($row) {
                return $row->sequence->name ?? '';
            }, 'SEQUENCE'),
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
            'machine_uuid',
            'sequence_uuid',
            'name',
        ], [
            new OptionData(
                'A',
                Machine::
                    when(auth()->user() && !auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($where) {
                        $where->whereHas('unit.location.subArea', fn($query) => $query->where('area_uuid', '=', auth()->user()->area_uuid));
                    })
                    ->with(['unit.location'])
                    ->get()
                    ->map(function ($row) {
                        $unit = $row->unit?->name;
                        $location = $row->unit?->location?->name;
                        $location = $row->unit?->location?->name;
                        return "$location / $unit / $row->name / $row->uuid";
                    })
                    ->values()
                    ->toArray()
            ),
            new OptionData(
                'B',
                Sequence::pluck('name', 'uuid')
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
            $machine = str($data['machine_uuid'] ?? '')->explode('/')->toArray();
            $sequence = str($data['sequence_uuid'] ?? '')->explode('/')->toArray();
            InspectionType::create([
                'name' => $data['name'] ?? '',
                'machine_uuid' => trim(end($machine)),
                'sequence_uuid' => trim(end($sequence)),
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
