<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';
}
