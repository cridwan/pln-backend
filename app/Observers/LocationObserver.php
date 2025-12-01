<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\Location;

class LocationObserver
{
    public function deleting(Location $model)
    {
        $exists = $model->units()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
