<?php

namespace App\Models\Transaction;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ConsMat extends Model
{
    use SettingModel;

    protected $connection = 'transaction';

    protected $table = 'const_mats';

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }
}
