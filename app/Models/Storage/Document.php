<?php

namespace App\Models\Storage;

use App\Enums\ConnectionEnum;
use App\Observers\DocumentSafeObserver;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([DocumentSafeObserver::class])]
class Document extends Model
{
    use SettingModel;

    protected $connection = ConnectionEnum::DOCUMENT->value;
}
