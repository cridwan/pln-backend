<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class ScopeStandart extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function details()
    {
        return $this->hasMany(DetailScopeStandart::class, 'scope_standart_uuid');
    }

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class, 'inspection_type_uuid');
    }
}
