<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SequenceAnimation extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';
}
