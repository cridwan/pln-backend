<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\GlobalUnit;

class GlobalUnitObserver
{
    public function deleting(GlobalUnit $model)
    {
        $exists = $model->parts()->exists() || $model->materials()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
