<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Models\Storage\Document;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
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

    public function additionalScope()
    {
        return $this->belongsTo(AdditionalScope::class, 'additional_scope_uuid');
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

    public function scopeFromTransaction(Builder $builder)
    {
        if (request()->filled('from_transaction')) {
            $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
            $builder->whereNotExists(function ($subQuery) use ($trxDb) {
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.scope_standarts as trx')
                    ->whereColumn('trx.original_uuid', '=', 'scope_standarts.uuid');
                if (request()->filled('project_uuid')) {
                    $subQuery->where('trx.project_uuid', '=', request()->get('project_uuid'));
                }
            });

            if (request()->filled('from_add_scope')) {
                $builder->doesntHave('inspectionType');
            } else {
                $builder->doesntHave('additionalScope');
            }
        }
    }
}
