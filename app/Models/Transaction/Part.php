<?php

namespace App\Models\Transaction;

use App\Models\GlobalUnit;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Part extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'transaction';

    public function globalUnit(): BelongsTo
    {
        return $this->belongsTo(GlobalUnit::class, 'global_unit_uuid');
    }

    public function additionalScope()
    {
        return $this->belongsTo(AdditionalScope::class, 'additional_scope_uuid');
    }
}
