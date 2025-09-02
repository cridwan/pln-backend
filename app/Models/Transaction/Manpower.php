<?php

namespace App\Models\Transaction;

use App\Enums\ConnectionEnum;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manpower extends Model
{
    use SettingModel, HasFactory;

    protected $table = 'manpower_stds';

    protected $connection = ConnectionEnum::TRANSACTION->value;

    public function additionalScope()
    {
        return $this->belongsTo(AdditionalScope::class, 'additional_scope_uuid');
    }

    public function activity()
    {
        return $this->belongsTo(\App\Models\Activity::class, 'activity_uuid');
    }

    public function manpower()
    {
        return $this->belongsTo(\App\Models\Manpower::class, 'manpower_uuid');
    }
}
