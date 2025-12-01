<?php

namespace App\Observers;

use App\Exceptions\BadRequestException;
use App\Models\Bidang;

class BidangObserver
{
    public function deleting(Bidang $model)
    {
        $exists = $model->subBidangs()->exists();

        if ($exists) {
            throw new BadRequestException("Data {$model->name} sudah digunakan di data lain");
        }
    }
}
