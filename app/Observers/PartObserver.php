<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\Part;

class PartObserver
{
    public function deleting(Part $model)
    {
        $exists = $model->stds()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakana di data lain");
        }
    }
}
