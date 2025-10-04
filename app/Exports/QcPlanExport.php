<?php

namespace App\Exports;

use App\Models\Transaction\Project;
use App\Models\Transaction\QcPlan;


class QcPlanExport extends Export
{
    private int $number = 1;

    public function query()
    {
        return QcPlan::query()
            ->with(['project'])
            ->when($this->project, fn($query) => $query->where('project_uuid', '=', $this->project->uuid));
    }

    public function map($row): array
    {
        return [
            $this->number++,
            $row->project?->name ?? '',
            $row->name,
            $row->created_at->translatedFormat('d F Y')
        ];
    }

    public function headers(): array
    {
        return [
            'NO',
            'NAMA PROJECT',
            'QC PLAN',
            'DIBUAT'
        ];
    }
}
