<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_uuid');
    }
}
