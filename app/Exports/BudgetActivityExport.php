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
                'equipments.activities.manpowers.manpower',
                'equipments.activities.materials.consmat.globalUnit',
                'equipments.activities.parts.part.globalUnit'
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
                    $activity->name,
                    $activity->duration,
                ];

                $maxRow = max($activity->manpowers->count(), $activity->materials->count(), $activity->parts->count());


                for ($i = 0; $i < $maxRow; $i++) {
                    $data[] = [
                        ...collect(range(0, 10))->map(fn() => ''), // tambahkan offset kolom sesuai kebutuhan
                        // materials
                        $activity->materials[$i]?->consmat?->name ?? '',
                        $activity->materials[$i]?->qty ?? '',
                        $activity->materials[$i]?->consmat?->globalUnit?->name ?? '',
                        'Rp. ' . number_format($activity->materials[$i]?->consmat?->price ?? 0, 2),
                        'Rp. ' . number_format(($activity->materials[$i]?->consmat?->price ?? 0) * ($activity->materials[$i]?->qty ?? 0), 2),
                        // manpower
                        $activity->manpowers[$i]?->manpower?->name ?? '',
                        $activity->manpowers[$i]?->qty ?? '',
                        'Rp. ' . number_format($activity->manpowers[$i]?->manpower?->price ?? 0, 2),
                        'Rp. ' . number_format(($activity->manpowers[$i]?->manpower?->price ?? 0) * ($activity->manpowers[$i]?->qty ?? 0), 2),
                        // part
                        $activity->parts[$i]?->part?->name ?? '',
                        $activity->parts[$i]?->qty ?? '',
                        $activity->parts[$i]?->part?->globalUnit?->name ?? '',
                        'Rp. ' . number_format($activity->parts[$i]?->part?->price ?? 0, 2),
                        'Rp. ' . number_format(($activity->parts[$i]?->part?->price ?? 0) * ($activity->materials[$i]?->qty ?? 0), 2),
                    ];
                }
            }
        }

        return $data;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        parent::styles($sheet);

        foreach (range('A', 'K') as $column) {
            $sheet->mergeCells("{$column}5:{$column}6");
        }

        $sheet->mergeCells('L5:P5');
        $sheet->mergeCells('Q5:T5');
        $sheet->mergeCells('U5:Y5');
    }
}
