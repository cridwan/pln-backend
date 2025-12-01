<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\Unit;

class UnitObserver
{
    public function deleting(Unit $model)
    {
        $exists = $model->machines()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
