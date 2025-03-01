<?php

namespace App\Models\Transaction;

use App\Models\GlobalUnit;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tools extends Model
{
    use SettingModel;

    protected $connection = 'transaction';

    public function globalUnit(): BelongsTo
    {
        return $this->belongsTo(GlobalUnit::class, 'global_unit_uuid');
    }
}
