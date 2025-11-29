<?php

namespace App\Exports\Guest;

use App\Models\PartStd;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;


class PartExport extends Export implements WithColumnFormatting
{
    protected int $index = 0;

    public function headings(): array
    {
        return [
            'NO',
            'TYPE',
            'PART',
            'QTY',
            'UNIT'
        ];
    }

    public function query()
    {
        return PartStd::query()
            ->with(['part.globalUnit'])
            ->addSelect([
                'part_stds.part_uuid',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw("('SCOPE STANDART') AS type_scope")
            ])
            ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('inspection_type_uuid', $this->InspectionType->uuid))
            ->union(
                PartStd::query()
                    ->with(['part.globalUnit'])
                    ->addSelect([
                        'part_stds.part_uuid',
                        DB::raw('SUM(qty) as total_qty'),
                        DB::raw("('ADDITIONAL SCOPE') AS type_scope")
                    ])
                    ->whereHas('activity.equipment.scopeStandart.additionalScope', fn($query) => $query->where('inspection_type_uuid', $this->InspectionType->uuid))
                    ->groupBy('part_stds.part_uuid')
            )
            ->groupBy('part_stds.part_uuid')
            ->orderBy('type_scope', 'DESC');
    }

    public function map($row): array
    {
        return [
            ++$this->index,
            $row->type_scope,
            $row->part?->name ?? '-',
            (string) $row->total_qty ?? '0',
            (string) $row->part?->globalUnit?->name ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '"Rp" #,##0.00_-',
            'F' => '"Rp" #,##0.00_-',
        ];
    }
}
