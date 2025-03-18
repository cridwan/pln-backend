<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tools extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function globalUnit(): BelongsTo
    {
        return $this->belongsTo(GlobalUnit::class, 'global_unit_uuid');
    }

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class, 'inspection_type_uuid');
    }
}
