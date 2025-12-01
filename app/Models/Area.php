<?php

namespace App\Models;

use App\Observers\AreaObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class, AreaObserver::class])]
class Area extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function users()
    {
        return $this->hasMany(User::class, 'area_uuid');
    }

    public function subAreas()
    {
        return $this->hasMany(SubArea::class, 'area_uuid');
    }
}
