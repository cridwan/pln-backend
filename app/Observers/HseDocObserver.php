<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\HseDoc;

class HseDocObserver
{
    public function deleting(HseDoc $model)
    {
        $exists = $model->transactions()
            ->whereHas('project', function ($query) {
                $query->where('status', '!=', 'approve');
            })
            ->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di transaksi");
        }
    }
}
