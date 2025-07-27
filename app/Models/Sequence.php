<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class, 'inspection_type_uuid');
    }
}
