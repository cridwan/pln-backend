<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionType extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine_uuid');
    }

    public function sequence()
    {
        return $this->belongsTo(Sequence::class, 'sequence_uuid');
    }
}
