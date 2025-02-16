<?php

namespace App\Models\Transaction;

use App\Models\InspectionType;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use SettingModel;

    protected $connection = 'transaction';

    public function inspectionType(): BelongsTo
    {
        return $this->belongsTo(InspectionType::class, 'inspection_type_uuid');
    }
}
