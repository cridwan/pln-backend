<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\QcPlan;

class QcPlanObserver
{
    public function deleting(QcPlan $model)
    {
        $exists = $model->transactions()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di transaksi");
        }
    }
}
