<?php

namespace App\Models\Transaction;

use App\Enums\ConnectionEnum;
use App\Models\Storage\Document;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class QcPlan extends Model
{
    use SettingModel;

    protected $connection = ConnectionEnum::TRANSACTION->value;

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }

    public function additionalScope()
    {
        return $this->belongsTo(AdditionalScope::class, 'additional_scope_uuid');
    }
}
