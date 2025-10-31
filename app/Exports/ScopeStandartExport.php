<?php

namespace App\Exports;

use App\Models\Transaction\Activity;
use App\Models\Transaction\Equipment;
use App\Models\Transaction\ScopeStandart;
use Illuminate\Support\Facades\DB;

class ScopeStandartExport extends Export
{
    public function __construct(\App\Models\Transaction\Project $project, \App\Enums\ExportTypeEnum $exportType = \App\Enums\ExportTypeEnum::XLSX, public string $type = "SCOPE STANDART")
    {
        parent::__construct($project, $exportType);
    }

    protected $colorRanges = [];

    protected int $number = 1;

    public function headings(): array
    {
        return [
            [
                'NO',
                'SCOPE STANDART',
                'EQUIPMENT',
                'ACTIVITY',
                'DURASI',
                'CONDITION',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                '',
                '',
                '',
                '',
                '',
                'ASSET WELNESS',
                'OH RECOMMENDATION',
                'WO PRIORITY',
                'HISTORY',
                'RLA',
                'NCR',
            ]
        ];
    }

    public function query()
    {
        return ScopeStandart::query()
            ->with(['assetWelnes', 'ohRecom', 'woPriority', 'rla', 'ncr', 'history'])
            ->select([
                'scope_standarts.*',
                DB::raw("'SCOPE STANDART' as type")
            ])
            ->addSelect([
                'total_activities' => Equipment::selectRaw('COUNT(activities.uuid)')
                    ->join('activities', 'activities.equipment_uuid', '=', 'equipment.uuid')
                    ->whereColumn('equipment.scope_standart_uuid', 'scope_standarts.uuid'),
                'total_equipment' => Equipment::selectRaw('COUNT(uuid)')
                    ->whereColumn('equipment.scope_standart_uuid', 'scope_standarts.uuid')
            ])
            ->when($this->type == "SCOPE STANDART", function ($where) {
                $where->where('project_uuid', $this->project?->uuid);
            }, function ($where) {
                $where->whereHas('additionalScope', fn($as) => $as->where('project_uuid', '=', $this->project?->uuid));
            })
            ->orderBy('name', 'asc');
    }

    /**
     * @param ScopeStandart $row
     */
    public function map($row): array
    {
        $data = [];
        $equipments = Equipment::where('scope_standart_uuid', $row->uuid)->get();

        // Tambahkan nomor & nama scope di baris pertama
        $data[] = [
            $this->number++,
            $row->name,
            '',
            '',
            '',
            $row->assetWelnes?->note,
            $row->ohRecom?->note,
            $row->woPriority?->note,
            $row->history?->note,
            $row->rla?->note,
            $row->ncr?->note,
        ];

        $this->mappingEquipment($data, $equipments, $row);
        $this->colorRanges[] = [
            'row' => $row->total_equipment + $row->total_activities + 1,
            'color' => $row->assetWelnes?->color?->color()
        ];
        return $data;
    }

    protected function mappingEquipment(&$data, $equipments, $row)
    {
        foreach ($equipments as $equipment) {
            $activities = Activity::where('equipment_uuid', $equipment->uuid)->get();

            $data[] = [
                '',
                '',
                $equipment->name,
                '',
            ];
            // Jika equipment punya activity
            foreach ($activities as $activity) {
                $data[] = [
                    '', // no
                    '', // scope name
                    '',
                    $activity->name,
                    $activity->duration
                ];
            }
        }
    }


    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        parent::styles($sheet);

        foreach (range('A', 'E') as $column) {
            $sheet->mergeCells("{$column}5:{$column}6");
        }

        $sheet->mergeCells('F5:J5');
        $startColor = 7;
        foreach ($this->colorRanges as $index => $range) {
            $endColor = $startColor + $range['row'] - 1;
            $sheet->getStyle("F{$startColor}:F{$endColor}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $range['color']],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->mergeCells("F{$startColor}:F{$endColor}");
            $sheet->mergeCells("G{$startColor}:G{$endColor}");
            $sheet->mergeCells("H{$startColor}:H{$endColor}");
            $sheet->mergeCells("I{$startColor}:I{$endColor}");
            $sheet->mergeCells("J{$startColor}:J{$endColor}");
            $sheet->mergeCells("K{$startColor}:K{$endColor}");
            $startColor += $range['row'];
        }
    }

}
