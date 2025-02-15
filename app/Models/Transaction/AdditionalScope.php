<?php

namespace App\Models\Transaction;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class AdditionalScope extends Model
{
    use SettingModel;

    protected $connection = 'transaction';
}
