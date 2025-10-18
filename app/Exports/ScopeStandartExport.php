<?php

namespace App\Exports;

use App\Enums\ConnectionEnum;
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
        $baseQuery = ScopeStandart::query()
            ->select([
                'scope_standarts.*',
                DB::raw("'SCOPE STANDART' as type")
            ])
            ->where('project_uuid', $this->project?->uuid);

        $additionalQuery = ScopeStandart::query()
            ->select([
                'scope_standarts.*',
                DB::raw("'ADDITIONAL SCOPE' as type")
            ])
            ->whereHas('additionalScope', function ($query) {
                $query->where('project_uuid', $this->project?->uuid);
                // ->where(function ($q) {
                //     $q->has('assetWelnes')
                //         ->orHas('ohRecom')
                //         ->orHas('woPriority')
                //         ->orHas('history')
                //         ->orHas('rla')
                //         ->orHas('ncr');
                // });
            });

        $query = $baseQuery
            ->unionAll($additionalQuery);

        return DB::connection(ConnectionEnum::TRANSACTION->value)->query()
            ->fromSub($query, 'union_scope')
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->orderBy('type', 'DESC');
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
