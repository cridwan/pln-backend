<?php

namespace App\Models\Transaction;

use App\Enums\ColorTypeEnum;
use App\Enums\ConnectionEnum;
use App\Models\Storage\Document;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ScopeStandartAsset extends Model
{
    use SettingModel;

    protected $connection = ConnectionEnum::TRANSACTION->value;

    protected $table = 'scope_assets';

    protected $casts = [
        'color' => ColorTypeEnum::class
    ];

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }
}
