<?php

namespace App\Models;

use App\Observers\BidangObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class, BidangObserver::class])]
class Bidang extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function subBidangs()
    {
        return $this->hasMany(SubBidang::class, 'bidang_uuid');
    }
}
