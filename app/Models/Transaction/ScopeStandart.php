<?php

namespace App\Models\Transaction;

use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScopeStandart extends Model
{
    use SettingModel;

    protected $connection = 'transaction';

    public function details(): HasMany
    {
        return $this->hasMany(DetailScopeStandart::class, 'scope_standart_uuid');
    }

    public function assets()
    {
        return $this->hasMany(ScopeStandartAsset::class, 'scope_standart_uuid');
    }
}
