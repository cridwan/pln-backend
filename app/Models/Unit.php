<?php

namespace App\Models;

use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UppercaseObservser::class])]
class Unit extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_uuid');
    }
}
