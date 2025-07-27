<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class Hse extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function hseDoc()
    {
        return $this->belongsTo(HseDoc::class);
    }

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class);
    }
}
