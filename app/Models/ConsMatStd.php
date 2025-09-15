<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
class ConsMatStd extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    protected $table = 'cons_mat_stds';

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid');
    }

    public function consmat()
    {
        return $this->belongsTo(ConsMat::class, 'cons_mat_uuid');
    }

    public function scopeFromTransaction(Builder $builder)
    {
        if (request()->filled('from_transaction')) {
            $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
            $builder->whereNotExists(function ($subQuery) use ($trxDb) {
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.cons_mat_stds as trx')
                    ->join($trxDb . '.activities as ac', 'ac.uuid', '=', 'trx.activity_uuid')
                    ->join($trxDb . '.equipment as eq', 'eq.uuid', '=', 'ac.equipment_uuid')
                    ->join($trxDb . '.scope_standarts as ss', 'ss.uuid', '=', 'eq.scope_standart_uuid')
                    ->whereColumn('trx.original_uuid', '=', 'cons_mat_stds.uuid');
                if (request()->filled('project_uuid')) {
                    $subQuery->where('ss.project_uuid', '=', request()->get('project_uuid'));
                } else if (request()->filled('additional_scope_uuid')) {
                    $subQuery->where('ss.additional_scope_uuid', '=', request()->get('additional_scope_uuid'));
                }
            });

            if (request()->filled('from_add_scope')) {
                $builder
                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('additional_scope_uuid', '=', request()->get('original_uuid')))
                    ->doesntHave('activity.equipment.scopeStandart.inspectionType');
            } else {
                $builder->doesntHave('activity.equipment.scopeStandart.additionalScope');
            }
        }
    }
}
