<?php

namespace App\Exports;

use App\Models\Transaction\ScopeStandart;
use Maatwebsite\Excel\Concerns\FromCollection;

class BudgetActivityExport extends Export
{
    private int $number = 1;

    public function headers(): array
    {
        return [
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
            'NAMA MATERIAL',
            'JUMLAH',
            'SATUAN',
            'MANPOWER',
            'JUMLAH',
        ];
    }

    public function query()
    {
        return ScopeStandart::query()
            ->with([
                'subBidang.bidang',
                'equipments.activities',
                'equipments.activities.manpowers.manpower',
                'equipments.activities.materials.consmat.globalUnit'
            ])
            ->when($this->project, fn($query) => $query->where('project_uuid', '=', $this->project->uuid));
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

                $maxRow = max($activity->manpowers->count(), $activity->materials->count());

                for ($i = 0; $i < $maxRow; $i++) {
                    $data[] = [
                        ...collect(range(0, 10))->map(fn() => ''), // tambahkan offset kolom sesuai kebutuhan
                        $activity->materials[$i]?->consmat?->name ?? '',
                        $activity->materials[$i]?->qty ?? '',
                        $activity->materials[$i]?->consmat?->globalUnit?->name ?? '',
                        $activity->manpowers[$i]?->manpower?->name ?? '',
                        $activity->manpowers[$i]?->qty ?? '',
                    ];
                }
            }
        }

        return $data;
    }
}
