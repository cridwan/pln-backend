<?php

namespace App\Exports;

use App\Models\Transaction\Manpower;
use App\Models\Transaction\Part;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class PartExport extends Export implements WithColumnFormatting, WithEvents
{
    protected int $index = 0;

    public function headers(): array
    {
        return [
            'NO',
            'TYPE',
            'PART',
            'QTY',
            'HARGA',
            'TOTAL',
        ];
    }

    public function query()
    {
        return Part::query()
            ->with(['part'])
            ->addSelect([
                'part_stds.part_uuid',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw("('SCOPE STANDART') AS type_scope")
            ])
            ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('project_uuid', $this->project->uuid))
            ->union(
                Part::query()
                    ->with(['part'])
                    ->addSelect([
                        'part_stds.part_uuid',
                        DB::raw('SUM(qty) as total_qty'),
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
            (string) $row->part?->price ?? '0',
            (string) (($row->part?->price ?? 0) * $row->total_qty) ?? '0',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '"Rp" #,##0.00_-',
            'F' => '"Rp" #,##0.00_-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();   // Row terakhir data
    
                // Misal kolom yang akan di-SUM adalah kolom B & C
                $sumRow = $highestRow + 1; // Baris SUM baru
    
                // Menulis formula SUM
                $sheet->setCellValue("C{$sumRow}", 'TOTAL');
                $sheet->getStyle("C{$sumRow}:C{$sumRow}")->getFont()->setBold(true);
                collect(['D', 'E', 'F'])->map(function ($column) use ($sheet, $sumRow, $highestRow) {
                    $sheet->setCellValue("{$column}{$sumRow}", "=SUM({$column}7:{$column}{$highestRow})");
                    $sheet->getStyle("{$column}{$sumRow}:{$column}{$sumRow}")->getNumberFormat()
                        ->setFormatCode($column == "D" ? '#,##0' : '"Rp" #,##0.00_-');
                    $sheet->getStyle("{$column}{$sumRow}:{$column}{$sumRow}")->getFont()->setBold(true);
                });
            },
        ];
    }
}
