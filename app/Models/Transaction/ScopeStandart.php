<?php

namespace App\Models\Transaction;

use App\Enums\ScopeStandartTypeEnum;
use App\Models\Storage\Document;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ScopeStandart extends Model
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

    public function details(): HasMany
    {
        return $this->hasMany(DetailScopeStandart::class, 'scope_standart_uuid');
    }

    public function assetWelnes(): HasOne
    {
        return $this->hasOne(ScopeStandartAsset::class, 'scope_standart_uuid')->where('category', ScopeStandartTypeEnum::ASSET_WELNESS->value)->latest();
    }

    public function ohRecom(): HasOne
    {
        return $this->hasOne(ScopeStandartAsset::class, 'scope_standart_uuid')->where('category', ScopeStandartTypeEnum::OH_RECOM->value)->latest();
    }

    public function woPriority(): HasOne
    {
        return $this->hasOne(ScopeStandartAsset::class, 'scope_standart_uuid')->where('category', ScopeStandartTypeEnum::WO_PRIORITY->value)->latest();
    }

    public function history(): HasOne
    {
        return $this->hasOne(ScopeStandartAsset::class, 'scope_standart_uuid')->where('category', ScopeStandartTypeEnum::HISTORY->value)->latest();
    }

    public function rla(): HasOne
    {
        return $this->hasOne(ScopeStandartAsset::class, 'scope_standart_uuid')->where('category', ScopeStandartTypeEnum::RLA->value)->latest();
    }

    public function ncr(): HasOne
    {
        return $this->hasOne(ScopeStandartAsset::class, 'scope_standart_uuid')->where('category', ScopeStandartTypeEnum::NCR->value)->latest();
    }
}
