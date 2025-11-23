<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Models\Storage\Document;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class Activity extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_uuid');
    }

    public function document()
    {
        return $this->morphOne(Document::class, 'document', 'document_type', 'document_uuid', 'uuid')->latest();
    }

    public function scopeFromTransaction(Builder $builder)
    {
        if (request()->filled('from_transaction')) {
            $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
            $builder->whereNotExists(function ($subQuery) use ($trxDb) {
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.activities as trx')
                    ->join($trxDb . '.equipment as eq', 'eq.uuid', '=', 'trx.equipment_uuid')
                    ->join($trxDb . '.scope_standarts as ss', 'ss.uuid', '=', 'eq.scope_standart_uuid')
                    ->whereColumn('trx.original_uuid', '=', 'activities.uuid');
                if (request()->filled('project_uuid')) {
                    $subQuery->where('ss.project_uuid', '=', request()->get('project_uuid'));
                } else if (request()->filled('additional_scope_uuid')) {
                    $subQuery->where('ss.additional_scope_uuid', '=', request()->get('additional_scope_uuid'));
                }
            });

            if (request()->filled('from_add_scope')) {
                $builder
                    ->whereHas('equipment.scopeStandart', fn($query) => $query->where('additional_scope_uuid', request()->get('original_uuid')))
                    ->doesntHave('equipment.scopeStandart.inspectionType');
            } else {
                $builder->doesntHave('equipment.scopeStandart.additionalScope');
            }
        }
    }
}
