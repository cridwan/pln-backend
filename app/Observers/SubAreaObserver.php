<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\SubArea;

class SubAreaObserver
{
    public function deleting(SubArea $model)
    {
        $exists = $model->locations()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
