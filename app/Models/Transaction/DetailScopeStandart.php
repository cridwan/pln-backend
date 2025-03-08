<?php

namespace App\Models\Transaction;

use App\Models\Storage\Document;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class DetailScopeStandart extends Model
{
    use SettingModel;

    protected $connection = 'transaction';


    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }
}
