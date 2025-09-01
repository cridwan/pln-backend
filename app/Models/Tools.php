<?php

namespace App\Models;

use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UppercaseObservser::class])]
class Tools extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function globalUnit(): BelongsTo
    {
        return $this->belongsTo(GlobalUnit::class, 'global_unit_uuid');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid');
    }
}
