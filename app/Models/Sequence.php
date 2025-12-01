<?php

namespace App\Models;

use App\Models\Storage\Document;
use App\Observers\SequenceObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class, SequenceObserver::class])]
class Sequence extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function document()
    {
        return $this->morphOne(Document::class, 'document', 'document_type', 'document_uuid', 'uuid')->latest();
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'document', 'document_type', 'document_uuid', 'uuid');
    }

    public function inspections()
    {
        return $this->hasMany(InspectionType::class, 'sequence_uuid');
    }

    public function scopes()
    {
        return $this->hasMany(AdditionalScope::class, 'sequence_uuid');
    }
}
