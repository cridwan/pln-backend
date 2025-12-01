<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\ConsMat;

class ConsumableObserver
{
    public function deleting(ConsMat $model)
    {
        $exists = $model->stds()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakana di data lain");
        }
    }
}
