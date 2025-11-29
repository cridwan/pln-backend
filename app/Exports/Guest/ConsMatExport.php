<?php

namespace App\Exports\Guest;

use App\Models\ConsMatStd;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;


class ConsMatExport extends Export implements WithColumnFormatting
{
    protected int $index = 0;

    public function headings(): array
    {
        return [
            'NO',
            'TYPE',
            'CONSUMABLE MATERIAL',
            'QTY',
            'UNIT',
        ];
    }

    public function query()
    {
        return ConsMatStd::query()
            ->with(['consmat.globalUnit'])
            ->addSelect([
                'cons_mat_stds.cons_mat_uuid',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw("('SCOPE STANDART') AS type_scope")
            ])
            ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('inspection_type_uuid', $this->InspectionType->uuid))
            ->union(
                ConsMatStd::query()
                    ->with(['consmat.globalUnit'])
                    ->addSelect([
                        'cons_mat_stds.cons_mat_uuid',
                        DB::raw('SUM(qty) as total_qty'),
                        DB::raw("('ADDITIONAL SCOPE') AS type_scope")
                    ])
                    ->whereHas('activity.equipment.scopeStandart.additionalScope', fn($query) => $query->where('inspection_type_uuid', $this->InspectionType->uuid))
                    ->groupBy('cons_mat_stds.cons_mat_uuid')
            )
            ->groupBy('cons_mat_stds.cons_mat_uuid')
            ->orderBy('type_scope', 'DESC');
    }

    public function map($row): array
    {
        return [
            ++$this->index,
            $row->type_scope,
            $row->consmat?->name ?? '-',
            (string) $row->total_qty ?? '0',
            (string) $row->consmat?->GlobalUnit?->name ?? '-',
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
