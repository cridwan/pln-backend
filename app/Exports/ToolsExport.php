<?php

namespace App\Exports;

use App\Models\Transaction\Tools;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;


class ToolsExport extends Export implements WithEvents
{
    protected int $index = 0;

    public function headings(): array
    {
        return [
            'NO',
            'TYPE',
            'TOOLS',
            'QTY',
            'HARGA',
            'TOTAL',
        ];
    }

    public function query()
    {
        return Tools::query()
            ->addSelect([
                'name',
                'merk',
                'status',
                'unit',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(price) as price'),
                DB::raw("('SCOPE STANDART') AS type_scope")
            ])
            ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('project_uuid', $this->project->uuid))
            ->union(
                Tools::query()
                    ->addSelect([
                        'name',
                        'merk',
                        'status',
                        'unit',
                        DB::raw('SUM(qty) as total_qty'),
                        DB::raw('SUM(price) as price'),
                        DB::raw("('ADDITIONAL SCOPE') AS type_scope")
                    ])
                    ->whereHas('activity.equipment.scopeStandart.additionalScope', function ($query) {
                        $query->has('assetWelnes')
                            ->orHas('ohRecom')
                            ->orHas('woPriority')
                            ->orHas('history')
                            ->orHas('rla')
                            ->orHas('ncr');
                    })
                    ->whereHas('activity.equipment.scopeStandart.additionalScope', fn($query) => $query->where('project_uuid', $this->project->uuid))
                    ->groupBy('name', 'merk', 'status', 'unit')
            )
            ->groupBy('name', 'merk', 'status', 'unit')
            ->orderBy('type_scope', 'DESC');
    }

    public function map($row): array
    {
        return [
            ++$this->index,
            $row->type_scope,
            $row->name ?? '-',
            (string) $row->total_qty ?? '0',
            (string) $row->price ?? '0',
            (string) (($row->price ?? 0) * $row->total_qty) ?? '0',
        ];
    }

    // public function columnFormats(): array
    // {
    //     return [
    //         'E' => '"Rp" #,##0.00_-',
    //         'F' => '"Rp" #,##0.00_-',
    //     ];
    // }
}
