<?php

namespace App\Exports\Guest;

use App\Models\ScopeStandart;

class BudgetActivityExport extends Export
{
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
                'MANPOWER',
                '',
                'PART',
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
                'TOTAL',
                'NAMA MANPOWER',
                'JUMLAH',
                'NAMA PART',
                'JUMLAH',
                'SATUAN',
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
            ->where('inspection_type_uuid', '=', $this->InspectionType->uuid)
            ->orWhereHas('additionalScope', function ($where) {
                $where->where('inspection_type_uuid', '=', $this->InspectionType->uuid);
            });
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

                $maxRow = max($activity->manpowers->count(), $activity->materials->count(), $activity->parts->count());


                for ($i = 0; $i < $maxRow; $i++) {
                    $data[] = [
                        ...collect(range(0, 10))->map(fn() => ''), // tambahkan offset kolom sesuai kebutuhan
                        // materials
                        $activity->materials[$i]?->consmat?->name ?? '',
                        $activity->materials[$i]?->qty ?? '',
                        $activity->materials[$i]?->consmat?->globalUnit?->name ?? '',
                        // manpower
                        $activity->manpowers[$i]?->manpower?->name ?? '',
                        $activity->manpowers[$i]?->qty ?? '',
                        // part
                        $activity->parts[$i]?->part?->name ?? '',
                        $activity->parts[$i]?->qty ?? '',
                        $activity->parts[$i]?->part?->globalUnit?->name ?? '',
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

        $sheet->mergeCells('L5:N5');
        $sheet->mergeCells('O5:P5');
        $sheet->mergeCells('Q5:S5');
    }
}
