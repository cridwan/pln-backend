<?php

namespace App\Models\Transaction;

use App\Enums\ConnectionEnum;
use App\Models\Storage\Document;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Activity extends Model
{
    use SettingModel;

    protected $connection = ConnectionEnum::TRANSACTION->value;

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'document', 'document_type'::class, 'document_uuid')->latest();
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_uuid');
    }

    public function materials()
    {
        return $this->hasMany(ConsMat::class, 'activity_uuid');
    }

    public function manpowers()
    {
        return $this->hasMany(Manpower::class, 'activity_uuid');
    }

    public function parts()
    {
        return $this->hasMany(Part::class, 'activity_uuid');
    }

    public function tools()
    {
        return $this->hasMany(Tools::class, 'activity_uuid');
    }
}
