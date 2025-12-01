<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\SubBidang;

class SubBidangObserver
{
    public function deleting(SubBidang $model)
    {
        $exists = $model->scopes()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
