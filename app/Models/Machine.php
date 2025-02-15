<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use SettingModel, HasFactory;

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_uuid');
    }
}
