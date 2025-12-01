<?php

namespace App\Models;

use App\Observers\GlobalUnitObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class, GlobalUnitObserver::class])]
class GlobalUnit extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function parts()
    {
        return $this->hasMany(Part::class, 'global_unit_uuid');
    }

    public function materials()
    {
        return $this->hasMany(ConsMat::class, 'global_unit_uuid');
    }
}
