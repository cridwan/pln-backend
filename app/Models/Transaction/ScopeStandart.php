<?php

namespace App\Models\Transaction;

use App\Enums\ConnectionEnum;
use App\Enums\ScopeStandartTypeEnum;
use App\Models\Storage\Document;
use App\Models\SubBidang;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ScopeStandart extends Model
{
    use SettingModel;

    protected $connection = ConnectionEnum::TRANSACTION->value;

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'document', 'document_type'::class, 'document_uuid', 'uuid')->latest();
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

    public function additionalScope()
    {
        return $this->belongsTo(AdditionalScope::class, 'additional_scope_uuid');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_uuid');
    }

    public function subBidang()
    {
        return $this->belongsTo(SubBidang::class, 'sub_bidang_uuid');
    }

    public function equipments()
    {
        return $this->hasMany(Equipment::class, 'scope_standart_uuid');
    }
}
