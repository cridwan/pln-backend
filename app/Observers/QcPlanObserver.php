<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\QcPlan;

class QcPlanObserver
{
    public function deleting(QcPlan $model)
    {
        // Cek apakah ada transaksi yang terkait dengan project yang statusnya approve
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
