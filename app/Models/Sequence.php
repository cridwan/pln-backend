<?php

namespace App\Models;

use App\Models\Storage\Document;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function document()
    {
        return $this->morphOne(Document::class, 'model', 'model_type', 'model_uuid', 'uuid')->latest();
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'model', 'model_type', 'model_uuid', 'uuid');
    }
}
