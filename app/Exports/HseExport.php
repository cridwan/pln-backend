<?php

namespace App\Exports;

use App\Models\Transaction\HseDoc;
use App\Models\Transaction\Project;

class HseExport extends Export
{
    private int $number = 1;

    public function headings(): array
    {
        return [
            'NO',
            'NAMA PROJECT',
            'HSE',
            'DIBUAT'
        ];
    }

    public function query()
    {
        return HseDoc::query()
            ->with(['parent', 'project'])
            ->when($this->project, fn($query) => $query->where('project_uuid', '=', $this->project->uuid));
    }
    public function map($row): array
    {
        return [
            $this->number++,
            $row->project?->name ?? '',
            $row->parent?->name ?? '',
            $row->created_at->translatedFormat('d F Y')
        ];
    }
}
