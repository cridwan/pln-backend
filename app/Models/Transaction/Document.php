<?php

namespace App\Models\Transaction;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use SettingModel;

    protected $connection = 'transaction';

    public function document()
    {
        return $this->morphTo();
    }
}
