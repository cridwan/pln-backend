<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\Sequence;

class SequenceObserver
{
    public function deleting(Sequence $model)
    {
        $exists = $model->inspections()->exists() || $model->scopes()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
