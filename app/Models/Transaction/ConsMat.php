<?php

namespace App\Models\Transaction;

use App\Models\GlobalUnit;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ConsMat extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'transaction';

    protected $table = 'const_mats';

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }

    public function globalUnit()
    {
        return $this->belongsTo(GlobalUnit::class, 'global_unit_uuid');
    }
}
