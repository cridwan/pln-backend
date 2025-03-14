<?php

namespace App\Models\Transaction;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manpower extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'transaction';

    public function additionalScope()
    {
        return $this->belongsTo(AdditionalScope::class, 'additional_scope_uuid');
    }
}
