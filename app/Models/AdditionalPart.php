<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class AdditionalPart extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';
}
