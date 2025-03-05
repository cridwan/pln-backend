<?php

namespace App\Exports;

use App\Models\Transaction\ConsMat;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ConsMatExport implements FromView
{
    public function query()
    {
        $query =  ConsMat::query();
        $query->with('globalUnit');
        return $query->get();
    }


    public function view(): View
    {
        return view('export.consmat', [
            'data' => $this->query()
        ]);
    }
}
