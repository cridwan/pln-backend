<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    use SettingModel, HasFactory;

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_uuid');
    }
}
