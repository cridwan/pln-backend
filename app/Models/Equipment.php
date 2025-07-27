<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function scopeStandart()
    {
        return $this->belongsTo(ScopeStandart::class, 'scope_standart_uuid');
    }
}
