<?php

namespace App\Exports;

use App\Enums\ScopeStandartCategoryEnum;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ScopeStandartSheetExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new ScopeStandartExport(ScopeStandartCategoryEnum::LISTRIK->value, 'Scope Listrik');
        $sheets[] = new ScopeStandartExport(ScopeStandartCategoryEnum::MEKANIK->value, 'Scope Mekanik');
        $sheets[] = new ScopeStandartExport(ScopeStandartCategoryEnum::INSTRUMENT->value, 'Scope Instrument');

        return $sheets;
    }
}
