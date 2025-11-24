<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use DB;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
/**
 * @method \Illuminate\Database\Eloquent\Builder<static> doestHaveTransaction(?string $inspectionType = null, ?string $activity = null)
 */
class PartStd extends Model
{

    use SettingModel;

    protected $connection = 'masterdata';

    protected $table = 'part_stds';


    protected $appends = [
        'use_transaction'
    ];


    public function getUseTransactionAttribute()
    {
        return DB::connection(ConnectionEnum::TRANSACTION->value)->table('part_stds')->where('original_uuid', $this->uuid)->exists();
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid');
    }

    public function part()
    {
        return $this->belongsTo(Part::class, 'part_uuid');
    }

    public function scopeFromTransaction(Builder $builder)
    {
        if (request()->filled('from_transaction')) {
            $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
            $builder->whereNotExists(function ($subQuery) use ($trxDb) {
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.part_stds as trx')
                    ->join($trxDb . '.activities as ac', 'ac.uuid', '=', 'trx.activity_uuid')
                    ->join($trxDb . '.equipment as eq', 'eq.uuid', '=', 'ac.equipment_uuid')
                    ->join($trxDb . '.scope_standarts as ss', 'ss.uuid', '=', 'eq.scope_standart_uuid')
                    ->whereColumn('trx.original_uuid', '=', 'part_stds.uuid');
                if (request()->filled('project_uuid')) {
                    $subQuery->where('ss.project_uuid', '=', request()->get('project_uuid'));
                } else if (request()->filled('additional_scope_uuid')) {
                    $subQuery->where('ss.additional_scope_uuid', '=', request()->get('additional_scope_uuid'));
                }
            });

            if (request()->filled('from_add_scope')) {
                $builder
                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('additional_scope_uuid', request()->get('original_uuid')))
                    ->doesntHave('activity.equipment.scopeStandart.inspectionType');
            } else {
                $builder->doesntHave('activity.equipment.scopeStandart.additionalScope');
            }
        }
    }

    public function scopeDoestHaveTransaction(Builder $builder, ?string $inspectionType = null, ?string $activity = null)
    {
        $builder->when($inspectionType, function ($query) use ($inspectionType, $activity) {
            $query
                ->has('activity.equipment.scopeStandart.inspectionType')
                ->whereNotExists(function ($sub) use ($inspectionType) {
                    $trxDb = DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                    $sub->selectRaw(1)
                        ->from("{$trxDb}.part_stds as trx")
                        ->leftJoin("{$trxDb}.activities as ac", "ac.uuid", "=", "trx.activity_uuid")
                        ->leftJoin("{$trxDb}.equipment as eq", "eq.uuid", "=", "ac.equipment_uuid")
                        ->leftJoin("{$trxDb}.scope_standarts as scope", "scope.uuid", "=", "eq.scope_standart_uuid")
                        ->leftJoin("{$trxDb}.projects as p", "p.uuid", "=", "scope.project_uuid")
                        ->whereRaw('trx.original_uuid = part_stds.uuid')
                        ->where("p.inspection_type_uuid", "=", $inspectionType);
                })
                ->whereHas('activity.equipment.scopeStandart', function ($where) use ($inspectionType) {
                    $where->where('inspection_type_uuid', '=', $inspectionType);
                })
                ->when($activity, function ($where) use ($activity) {
                    $where->where('activity_uuid', '=', $activity);
                });
        });
    }
}
