<?php

namespace App\Models;

use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class Activity extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_uuid');
    }
}
