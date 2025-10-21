<?php

namespace App\Models\Transaction;

use App\Enums\ConnectionEnum;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class ProjectActivity extends Model
{
    use SettingModel;

    protected $connection = ConnectionEnum::TRANSACTION->value;
}
