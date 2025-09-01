<?php

namespace App\Models;

use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class ConsMatStd extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    protected $table = 'cons_mat_stds';

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid');
    }

    public function consmat()
    {
        return $this->belongsTo(ConsMat::class, 'cons_mat_uuid');
    }
}
