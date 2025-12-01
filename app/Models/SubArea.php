<?php

namespace App\Models;

use App\Observers\SubAreaObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class, SubAreaObserver::class])]
class SubArea extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'sub_area_uuid');
    }
}
