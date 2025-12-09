<?php

namespace App\Models;

use App\Observers\GeneratorTypeObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class, GeneratorTypeObserver::class])]
class GeneratorType extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function locations()
    {
        return $this->hasMany(Location::class, 'generator_type_uuid');
    }
}
