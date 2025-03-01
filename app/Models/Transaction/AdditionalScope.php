<?php

namespace App\Models\Transaction;

use App\Enums\ScopeStandartTypeEnum;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AdditionalScope extends Model
{
    use SettingModel;

    protected $connection = 'transaction';

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

    public function sequenceAnimation(): HasOne
    {
        return $this->hasOne(SequenceAnimation::class, 'additional_scope_uuid');
    }
}
