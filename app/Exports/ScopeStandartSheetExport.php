<?php

namespace App\Exports;

use App\Enums\ScopeStandartCategoryEnum;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ScopeStandartSheetExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        public $project,
    ) {}

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new ScopeStandartExport(ScopeStandartCategoryEnum::LISTRIK->value, 'Scope Listrik', $this->project);
        $sheets[] = new ScopeStandartExport(ScopeStandartCategoryEnum::MEKANIK->value, 'Scope Mekanik', $this->project);
        $sheets[] = new ScopeStandartExport(ScopeStandartCategoryEnum::INSTRUMENT->value, 'Scope Instrument', $this->project);

        return $sheets;
    }
}
