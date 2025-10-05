<?php

namespace App\Exports;

use App\Models\Transaction\Activity;
use App\Models\Transaction\Equipment;
use App\Models\Transaction\ScopeStandart;
use Illuminate\Support\Facades\DB;

class ScopeStandartExport extends Export
{
    public int $number = 1;

    public function headers(): array
    {
        return [
            'NO',
            'SCOPE STANDART',
            'EQUIPMENT',
            'ACTIVITY',
            'DURASI'
        ];
    }

    public function query()
    {
        return ScopeStandart::query()
            ->addSelect([
                "scope_standarts.*",
                DB::raw("('SCOPE STANDART') as type")
            ])
            ->where('project_uuid', $this->project?->uuid)
            ->union(
                query: ScopeStandart::query()
                    ->addSelect([
                        'scope_standarts.*',
                        DB::raw("('ADDITIONAL SCOPE') AS type")
                    ])
                    ->whereHas('additionalScope', function ($query) {
                        $query->has('assetWelnes')
                            ->orHas('ohRecom')
                            ->orHas('woPriority')
                            ->orHas('history')
                            ->orHas('rla')
                            ->orHas('ncr')
                            ->where('project_uuid', $this->project?->uuid);
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
            $row->name,
            '',
            ''
        ];

        foreach ($equipments as $equipment) {
            $activities = Activity::where('equipment_uuid', $equipment->uuid)->get();

            $data[] = [
                '',
                '',
                $equipment->name,
                '',
            ];
            // Jika equipment punya activity
            foreach ($activities as $index => $activity) {
                $data[] = [
                    '', // no
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
