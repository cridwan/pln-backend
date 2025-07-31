<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class PartStd extends Model
{

    use SettingModel;

    protected $connection = 'masterdata';

    protected $table = 'part_stds';

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid');
    }

    public function part()
    {
        return $this->belongsTo(Part::class, 'part_uuid');
    }
}
