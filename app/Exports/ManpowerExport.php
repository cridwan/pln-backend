<?php

namespace App\Exports;

use App\Models\Transaction\Manpower;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ManpowerExport implements FromView
{
    public function query()
    {
        $query =  Manpower::query();
        return $query->get();
    }


    public function view(): View
    {
        return view('export.manpower', [
            'data' => $this->query()
        ]);
    }
}
