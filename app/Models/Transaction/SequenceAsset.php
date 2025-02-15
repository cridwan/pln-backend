<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class SequenceAsset extends Model
{
    use SettingModel;

    protected $connection = 'transaction';
}
