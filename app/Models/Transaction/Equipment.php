<?php

namespace App\Models\Transaction;

use App\Enums\ConnectionEnum;
use App\Models\Storage\Document;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Equipment extends Model
{
    use SettingModel;

    protected $connection = ConnectionEnum::TRANSACTION->value;

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }

    public function scopeStandart()
    {
        return $this->belongsTo(ScopeStandart::class, 'scope_standart_uuid');
    }
}
