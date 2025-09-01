<?php

namespace App\Models;

use App\Models\Storage\Document;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class ScopeStandart extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function details()
    {
        return $this->hasMany(DetailScopeStandart::class, 'scope_standart_uuid');
    }

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class, 'inspection_type_uuid');
    }

    public function subBidang()
    {
        return $this->belongsTo(SubBidang::class, 'sub_bidang_uuid');
    }

    public function document()
    {
        return $this->morphOne(Document::class, 'document', 'document_type', 'document_uuid', 'uuid')->latest();
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'document', 'document_type', 'document_uuid', 'uuid');
    }
}
