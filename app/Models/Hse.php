<?php

namespace App\Models;

use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
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
