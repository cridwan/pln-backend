<?php

namespace App\Core\Master\Detail;

use App\Core\MasterCore;
use App\Data\AttributeData;
use App\Data\OptionData;
use App\Data\TemplateData;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Activity;
use App\Models\Tools;
use App\Models\ToolsStd;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class ToolStdCore extends MasterCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'tool.globalUnit',
            'activity',
            'activity.equipment',
            'activity.equipment.scopeStandart',
            'activity.equipment.scopeStandart.inspectionType',
            'activity.equipment.scopeStandart.inspectionType.machine',
            'activity.equipment.scopeStandart.inspectionType.machine.unit',
            'activity.equipment.scopeStandart.inspectionType.machine.unit.location',
            'activity.equipment.scopeStandart.subBidang',
            'activity.equipment.scopeStandart.subBidang.bidang',
            'activityLog.createdBy',
            'activityLog.updatedBy',
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'tool.name',
            'asc'
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return ToolsStd::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return ToolsStd::query()
            ->has('activity.equipment.scopeStandart.additionalScope')
            ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('activity.equipment.scopeStandart.additionalScope.inspectionType.machine.unit.location.subArea', function ($where) {
                    $where->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            })
            ->hasTransactionDetail();
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'activity_uuid' => 'required|exists:activities,uuid',
            'tools_uuid' => 'required|exists:tools,uuid',
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
                return $row->activity?->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->location?->name ?? '';
            }, 'LOCATION'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->unit?->name ?? '';
            }, 'UNIT'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->additionalScope?->inspectionType?->machine?->name ?? '';
            }, 'MACHINE'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->additionalScope?->inspectionType?->name ?? '';
            }, 'INSPECTION TYPE'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->subBidang?->bidang?->name ?? '';
            }, 'BIDANG'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->subBidang?->name ?? '';
            }, 'SUB BIDANG'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->scopeStandart?->name ?? '';
            }, 'SCOPE STANDART'),
            new AttributeData(function ($row) {
                return $row->activity?->equipment?->name ?? '';
            }, 'EQUIPMENT'),
            new AttributeData(function ($row) {
                return $row->activity?->name ?? '';
            }, 'ACTIVITY'),
            new AttributeData(function ($row) {
                return $row->tool?->name ?? '';
            }, 'TOOLS'),
            new AttributeData(function ($row) {
                return $row->qty;
            }, 'QTY'),
            new AttributeData(function ($row) {
                return (string) $row->tool?->price;
            }, 'PRICE'),
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
            'activity_uuid',
            'tools_uuid',
            'qty',
        ], [
            new OptionData(
                'A',
                Activity::
                    when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                        $query->whereHas('equipment.scopeStandart.inspectionType.machine.unit.location.subArea', function ($where) {
                            $where->where('area_uuid', '=', auth()->user()->area_uuid);
                        });
                    })
                    ->with(['equipment.scopeStandart.inspectionType.machine.unit.location', 'equipment.scopeStandart.subBidang'])
                    ->get()
                    ->map(function ($row) {
                        $equipment = $row->equipment?->name ?? '';
                        $scope = $row->equipment?->scopeStandart?->name ?? '';
                        $inspectionType = $row->equipment?->scopeStandart?->inspectionType?->name ?? '';
                        $machine = $row->equipment?->scopeStandart?->inspectionType?->machine?->name ?? '';
                        $unit = $row->equipment?->scopeStandart?->inspectionType?->machine->unit?->name ?? '';
                        $location = $row->equipment?->scopeStandart?->inspectionType?->machine?->unit?->location?->name ?? '';
                        $subBidang = $row->equipment?->scopeStandart?->subBidang?->name ?? '';
                        return "$location / $unit / $machine/ $inspectionType / $subBidang / $scope / $equipment / $row->name / $row->uuid";
                    })
                    ->values()
                    ->toArray()
            ),
            new OptionData(
                'B',
                Tools::with('globalUnit')
                    ->get()
                    ->map(function ($row) {
                        $unit = $row->globalUnit?->name ?? '';
                        return "$row->name / $unit / $row->uuid";
                    })
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
            $activity = str($data['activity_uuid'] ?? '')->explode('/')->toArray();
            $tools = str($data['tools_uuid'] ?? '')->explode('/')->toArray();
            ToolsStd::create([
                'qty' => $data['qty'] ?? null,
                'activity_uuid' => trim(end($activity)),
                'tools_uuid' => trim(end($tools)),
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
