<?php

namespace App\Exports;

use App\Models\Transaction\Part;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PartExport implements FromView
{
    public function query()
    {
        $query =  Part::query();
        $query->with('globalUnit');
        return $query->get();
    }


    public function view(): View
    {
        return view('export.part', [
            'data' => $this->query()
        ]);
    }
}
