<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\Machine;

class MachineObserver
{
    public function deleting(Machine $model)
    {
        $exists = $model->inspections()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
