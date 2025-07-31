<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(Consmat::class, 'consmat_uuid');
    }
}
