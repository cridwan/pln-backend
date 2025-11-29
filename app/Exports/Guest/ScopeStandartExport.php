<?php

namespace App\Exports\Guest;

use App\Models\Activity;
use App\Models\Equipment;
use App\Models\ScopeStandart;
use Illuminate\Support\Facades\DB;

class ScopeStandartExport extends Export
{
    public int $number = 1;

    public function headings(): array
    {
        return [
            'NO',
            'TYPE',
            'SCOPE STANDART',
            'EQUIPMENT',
            'ACTIVITY',
            'DURATION'
        ];
    }

    public function query()
    {
        return ScopeStandart::query()
            ->addSelect([
                "scope_standarts.*",
                DB::raw("('SCOPE STANDART') as type")
            ])
            ->where('inspection_type_uuid', $this->InspectionType?->uuid)
            ->union(
                query: ScopeStandart::query()
                    ->addSelect([
                        'scope_standarts.*',
                        DB::raw("('ADDITIONAL SCOPE') AS type")
                    ])
                    ->whereHas('additionalScope', function ($query) {
                        $query
                            ->where('inspection_type_uuid', $this->InspectionType?->uuid);
                    })
            )->orderBy("type", "DESC");
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
            $row->type,
            $row->name,
            '',
            ''
        ];

        foreach ($equipments as $equipment) {
            $activities = Activity::where('equipment_uuid', $equipment->uuid)->get();

            $data[] = [
                '',
                '',
                '',
                $equipment->name,
                '',
            ];
            // Jika equipment punya activity
            foreach ($activities as $index => $activity) {
                $data[] = [
                    '', // no
                    '',
                    '', // scope name
                    '',
                    $activity->name,
                    $activity->duration
                ];
            }
        }

        return $data;
    }

}
