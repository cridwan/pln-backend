<?php

namespace App\Models;

use App\Observers\LocationObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class, LocationObserver::class])]
class Location extends Model
{
    use SettingModel, HasFactory;

    protected $appends = ['color'];

    protected $connection = 'masterdata';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_uuid', 'id');
    }

    public function getColorAttribute()
    {
        return $this->generatorType?->color;
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_uuid', 'id');
    }

    public function subArea()
    {
        return $this->belongsTo(SubArea::class, 'sub_area_uuid');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'location_uuid');
    }

    public function generatorType()
    {
        return $this->belongsTo(GeneratorType::class, 'generator_type_uuid');
    }
}
