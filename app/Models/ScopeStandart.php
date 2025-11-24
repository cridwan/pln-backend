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
/**
 * @method \Illuminate\Database\Eloquent\Builder<static> doestHaveTransaction(?string $inspectionType = null)
 */
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
                } else if (request()->filled('additional_scope_uuid')) {
                    $subQuery->where('trx.additional_scope_uuid', '=', request()->get('additional_scope_uuid'));
                }
            });

            if (request()->filled('from_add_scope')) {
                $builder
                    ->where('additional_scope_uuid', '=', request()->get('original_uuid'))
                    ->doesntHave('inspectionType');
            } else {
                $builder->doesntHave('additionalScope');
            }
        }
    }

    public function scopeDoestHaveTransaction(Builder $builder, ?string $inspectionType = null)
    {
        $builder->when($inspectionType, function ($query) use ($inspectionType) {
            $query
                ->has('inspectionType')
                ->whereNotExists(function ($sub) use ($inspectionType) {
                    $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                    $sub->selectRaw(1)
                        ->from("{$trxDb}.scope_standarts as trx")
                        ->leftJoin("{$trxDb}.projects as p", "p.uuid", "=", "trx.project_uuid")
                        ->whereRaw('trx.original_uuid = scope_standarts.uuid')
                        ->where("p.inspection_type_uuid", "=", $inspectionType);
                })
                ->where('inspection_type_uuid', '=', $inspectionType);
        });
    }
}
