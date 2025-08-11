<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class AdditionalScope extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function details()
    {
        return $this->hasMany(DetailScopeStandart::class, 'additional_scope_uuid');
    }

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class, 'inspection_type_uuid');
    }

    public function sequence()
    {
        return $this->belongsTo(Sequence::class, 'sequence_uuid');
    }
}
