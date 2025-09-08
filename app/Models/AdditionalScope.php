<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class AdditionalScope extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function details()
    {
        return $this->hasMany(DetailScopeStandart::class, 'additional_scope_uuid');
    }

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class, 'inspection_type_uuid');
    }

    public function sequence()
    {
        return $this->belongsTo(Sequence::class, 'sequence_uuid');
    }

    public function scopeFromTransaction(Builder $builder)
    {
        if (request()->filled('from_transaction')) {
            $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
            $builder->whereNotExists(function ($subQuery) use ($trxDb) {
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.additional_scopes as trx')
                    ->whereColumn('trx.original_uuid', '=', 'additional_scopes.uuid');
                if (request()->filled('project_uuid')) {
                    $subQuery->where('trx.project_uuid', '=', request()->get('project_uuid'));
                }
            });
        }
    }
}
