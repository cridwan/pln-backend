<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\GeneratorType;

class GeneratorTypeObserver
{
    public function deleting(GeneratorType $model)
    {
        $exists = $model->locations()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
