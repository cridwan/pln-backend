<?php

namespace App\Models;

use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class ManpowerStd extends Model
{

    use SettingModel;

    protected $connection = 'masterdata';

    protected $table = 'manpower_stds';

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid');
    }

    public function manpower()
    {
        return $this->belongsTo(Manpower::class, 'manpower_uuid');
    }
}
