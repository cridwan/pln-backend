<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class Equipment extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function scopeStandart()
    {
        return $this->belongsTo(ScopeStandart::class, 'scope_standart_uuid');
    }

    public function scopeFromTransaction(Builder $builder)
    {
        if (request()->filled('from_transaction')) {
            $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
            $builder->whereNotExists(function ($subQuery) use ($trxDb) {
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.equipment as trx')
                    ->join($trxDb . '.scope_standarts as ss', 'ss.uuid', '=', 'trx.scope_standart_uuid')
                    ->whereColumn('trx.original_uuid', '=', 'equipment.uuid');
                if (request()->filled('project_uuid')) {
                    $subQuery->where('ss.project_uuid', '=', request()->get('project_uuid'));
                }
            });

            if (request()->filled('from_add_scope')) {
                $builder->doesntHave('scopeStandart.inspectionType');
            } else {
                $builder->doesntHave('scopeStandart.additionalScope');
            }
        }
    }
}
