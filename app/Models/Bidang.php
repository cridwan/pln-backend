<?php

namespace App\Models;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';
}
