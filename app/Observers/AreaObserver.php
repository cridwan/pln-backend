<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\Area;

class AreaObserver
{
    public function deleting(Area $model)
    {
        $exists = $model->users()->exists() || $model->subAreas()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
