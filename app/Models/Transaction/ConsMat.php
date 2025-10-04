<?php

namespace App\Models\Transaction;

use App\Enums\ConnectionEnum;
use App\Models\GlobalUnit;
use App\Models\Storage\Document;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ConsMat extends Model
{
    use SettingModel, HasFactory;

    protected $connection = ConnectionEnum::TRANSACTION->value;

    protected $table = 'cons_mat_stds';

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }

    public function globalUnit()
    {
        return $this->belongsTo(GlobalUnit::class, 'global_unit_uuid');
    }

    public function additionalScope()
    {
        return $this->belongsTo(AdditionalScope::class, 'additional_scope_uuid');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid');
    }

    public function consmat()
    {
        return $this->belongsTo(\App\Models\ConsMat::class, 'cons_mat_uuid');
    }
}
