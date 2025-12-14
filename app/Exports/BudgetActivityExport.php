<?php

namespace App\Exports;

use App\Models\Transaction\ScopeStandart;

class BudgetActivityExport extends Export
{
    public function __construct(\App\Models\Transaction\Project $project, \App\Enums\ExportTypeEnum $exportType = \App\Enums\ExportTypeEnum::XLSX, public string $type = "SCOPE STANDART")
    {
        parent::__construct($project, $exportType);
    }
    private int $number = 1;

    public function headings(): array
    {
        return [
            [
                'NO',
                'UNIT',
                'BLOK',
                'MESIN',
                'TIPE MESIN',
                'SCOPE',
                'BIDANG',
                'SUB BIDANG',
                'EQUIPMENT',
                'NO URUT',
                'ACTIVITY',
                'DURASI',
                'MATERIAL',
                '',
                '',
                '',
                '',
                'MANPOWER',
                '',
                '',
                '',
                'PART',
                '',
                '',
                '',
                '',
                'TOOLS',
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
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'NAMA MATERIAL',
                'JUMLAH',
                'SATUAN',
                'HARGA',
                'TOTAL',
                'NAMA MANPOWER',
                'JUMLAH',
                'HARGA',
                'TOTAL',
                'NAMA PART',
                'JUMLAH',
                'SATUAN',
                'HARGA',
                'TOTAL',
                'NAMA TOOLS',
                'JUMLAH',
                'SATUAN',
                'HARGA',
                'TOTAL'
            ]
        ];
    }

    public function query()
    {
        return ScopeStandart::query()
            ->with([
                'subBidang.bidang',
                'equipments.activities',
                'equipments.activities.manpowers',
                'equipments.activities.materials',
                'equipments.activities.parts',
                'equipments.activities.tools',
            ])
            ->when(
                $this->type == 'SCOPE STANDART',
                fn($query) => $query->where('project_uuid', '=', $this->project->uuid),
                fn($query) => $query->whereHas('additionalScope', fn($as) => $as->where('project_uuid', '=', $this->project->uuid))
            );
    }

    /**
     * @param ScopeStandart $row
     */
    public function map($row): array
    {
        $data = [];

        $data[] = [
            $this->number++,
            $this->project?->inspectionType?->machine?->unit?->location?->name ?? '',
            $this->project?->inspectionType?->machine?->unit?->name ?? '',
            $this->project?->inspectionType?->machine?->name ?? '',
            $this->project?->inspectionType?->name ?? '',
            $row->name,
            $row->subBidang?->name,
            $row->subBidang?->bidang?->name,
        ];

        foreach ($row->equipments as $equipment) {
            $data[] = [
                ...collect(range(0, 7))->map(fn() => ''),
                $equipment->name,
            ];

            foreach ($equipment->activities as $activity) {
                $data[] = [
                    ...collect(range(0, 8))->map(fn() => ''),
                    $activity->serial_number,
                    $activity->name,
                    $activity->duration,
                ];
                $materials = $activity->materials;
                $manpowers = $activity->manpowers;
                $parts = $activity->parts;
                $tools = $activity->tools;


                $maxRow = max(
                    $materials->count(),
                    $manpowers->count(),
                    $parts->count(),
                    $tools->count()
                );

                for ($i = 0; $i < $maxRow; $i++) {

                    $material = $materials->get($i);
                    $manpower = $manpowers->get($i);
                    $part = $parts->get($i);
                    $tool = $tools->get($i);

                    $data[] = [
                        ...collect(range(0, 11))->map(fn() => ''),

                        // materials
                        $material?->name ?? '',
                        $material?->qty ?? '',
                        $material?->unit ?? '',
                        (string) ($material?->price ?? 0),
                        (string) (($material?->price ?? 0) * ($material?->qty ?? 0)),

                        // manpower
                        $manpower?->manpower?->name ?? '',
                        $manpower?->qty ?? '',
                        (string) ($manpower?->price ?? 0),
                        (string) (($manpower?->price ?? 0) * ($manpower?->qty ?? 0)),

                        // parts
                        $part?->name ?? '',
                        $part?->qty ?? '',
                        $part?->unit ?? '',
                        (string) ($part?->price ?? 0),
                        (string) (($part?->price ?? 0) * ($part?->qty ?? 0)),

                        // tools
                        $tool?->name ?? '',
                        $tool?->qty ?? '',
                        $tool?->unit ?? '',
                        (string) ($tool?->price ?? 0),
                        (string) (($tool?->price ?? 0) * ($tool?->qty ?? 0)),
                    ];
                }

            }
        }

        return $data;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        parent::styles($sheet);

        foreach (range('A', 'L') as $column) {
            $sheet->mergeCells("{$column}5:{$column}6");
        }

        $sheet->mergeCells('M5:Q5');
        $sheet->mergeCells('R5:U5');
        $sheet->mergeCells('V5:Z5');
        $sheet->mergeCells('AA5:AE5');
    }
}
