<?php

namespace App\Models\Transaction;

use App\Enums\ConnectionEnum;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class Tools extends Model
{
    use SettingModel;

    protected $connection = ConnectionEnum::TRANSACTION->value;

    protected $table = 'tools_stds';

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid');
    }
}
