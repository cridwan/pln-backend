<?php

namespace App\Models\Storage;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use SettingModel;

    protected $connection = 'document';
}
