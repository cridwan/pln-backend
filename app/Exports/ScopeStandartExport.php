<?php

namespace App\Exports;

use App\Models\Transaction\ScopeStandart;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ScopeStandartExport implements FromView
{
    public function query()
    {
        $query =  ScopeStandart::query();
        $query->with('details');
        return $query->get();
    }


    public function view(): View
    {
        return view('export.scope-standart', [
            'data' => $this->query()
        ]);
    }
}
