<?php

namespace App\Models\Transaction;

use App\Models\Storage\Document;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ScopeStandartAsset extends Model
{
    use SettingModel;

    protected $connection = 'transaction';

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }
}
