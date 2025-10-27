<?php

namespace App\Models;

use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class SubArea extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
