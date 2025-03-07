<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class ConsMat extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';
}
