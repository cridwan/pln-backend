<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Enums\DatabaseConnectionEnum;
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
class ManpowerStd extends Model
{

    use SettingModel;

    protected $connection = 'masterdata';

    protected $table = 'manpower_stds';


    protected $appends = [
        'use_transaction'
    ];


    public function getUseTransactionAttribute()
    {
        return DB::connection(ConnectionEnum::TRANSACTION->value)->table('manpower_stds')->where('original_uuid', $this->uuid)->exists();
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid');
    }

    public function manpower()
    {
        return $this->belongsTo(Manpower::class, 'manpower_uuid');
    }


    public function scopeFromTransaction(Builder $builder)
    {
        if (request()->filled('from_transaction')) {
            $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
            $builder->whereNotExists(function ($subQuery) use ($trxDb) {
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.manpower_stds as trx')
                    ->join($trxDb . '.activities as ac', 'ac.uuid', '=', 'trx.activity_uuid')
                    ->join($trxDb . '.equipment as eq', 'eq.uuid', '=', 'ac.equipment_uuid')
                    ->join($trxDb . '.scope_standarts as ss', 'ss.uuid', '=', 'eq.scope_standart_uuid')
                    ->whereColumn('trx.original_uuid', '=', 'manpower_stds.uuid');
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
                        ->from("{$trxDb}.manpower_stds as trx")
                        ->leftJoin("{$trxDb}.activities as ac", "ac.uuid", "=", "trx.activity_uuid")
                        ->leftJoin("{$trxDb}.equipment as eq", "eq.uuid", "=", "ac.equipment_uuid")
                        ->leftJoin("{$trxDb}.scope_standarts as scope", "scope.uuid", "=", "eq.scope_standart_uuid")
                        ->leftJoin("{$trxDb}.projects as p", "p.uuid", "=", "scope.project_uuid")
                        ->whereRaw('trx.original_uuid = manpower_stds.uuid')
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

    public function scopeDoestHaveTransactionDetail(Builder $builder, ?string $additionalScope = null)
    {
        $builder->when($additionalScope, function ($query) use ($additionalScope) {
            $query
                ->has('activity.equipment.scopeStandart.additionalScope')
                ->whereNotExists(function ($sub) use ($additionalScope) {
                    $trxDb = DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                    $sub->selectRaw(1)
                        ->from("{$trxDb}.manpower_stds as trx")
                        ->leftJoin("{$trxDb}.activities as ac", "ac.uuid", "=", "trx.activity_uuid")
                        ->leftJoin("{$trxDb}.equipment as eq", "eq.uuid", "=", "ac.equipment_uuid")
                        ->leftJoin("{$trxDb}.scope_standarts as scope", "scope.uuid", "=", "eq.scope_standart_uuid")
                        ->leftJoin("{$trxDb}.additional_scopes as ad_scope", "ad_scope.uuid", "=", "scope.additional_scope_uuid")
                        ->whereRaw('trx.original_uuid = manpower_stds.uuid')
                        ->where("ad_scope.original_uuid", "=", $additionalScope);
                })
                ->whereHas('activity.equipment.scopeStandart', function ($where) use ($additionalScope) {
                    $where->where('additional_scope_uuid', '=', $additionalScope);
                })
                ->when(request()->input('activity_uuid', null), function ($query) {
                    $query->where('activity_uuid', '=', request()->input('activity_uuid'));
                });
        });
    }

    public function scopeHasTransaction(Builder $builder)
    {
        $databaseName = DatabaseConnectionEnum::TRANSACTION->value;
        $builder->addSelect([
            'has_transaction' => DB::table("{$databaseName}.manpower_stds as trx")
                ->leftJoin("{$databaseName}.activities as ac", 'ac.uuid', '=', 'trx.activity_uuid')
                ->leftJoin("{$databaseName}.equipment as eq", 'eq.uuid', '=', 'ac.equipment_uuid')
                ->leftJoin("{$databaseName}.scope_standarts as scope", 'scope.uuid', '=', 'eq.scope_standart_uuid')
                ->leftJoin("{$databaseName}.projects", 'projects.uuid', '=', 'scope.project_uuid')
                ->whereColumn('trx.original_uuid', '=', 'manpower_stds.uuid')
                ->where('projects.status', '!=', 'approve')
                ->selectRaw('COUNT(projects.uuid)'),
        ]);
    }

    public function scopeHasTransactionDetail(Builder $builder)
    {
        $databaseName = DatabaseConnectionEnum::TRANSACTION->value;
        $builder->addSelect([
            'has_transaction' => DB::table("{$databaseName}.manpower_stds as trx")
                ->leftJoin("{$databaseName}.activities as ac", 'ac.uuid', '=', 'trx.activity_uuid')
                ->leftJoin("{$databaseName}.equipment as eq", 'eq.uuid', '=', 'ac.equipment_uuid')
                ->leftJoin("{$databaseName}.scope_standarts as scope", 'scope.uuid', '=', 'eq.scope_standart_uuid')
                ->leftJoin("{$databaseName}.additional_scopes as add_scope", 'add_scope.uuid', '=', 'scope.additional_scope_uuid')
                ->leftJoin("{$databaseName}.projects", 'projects.uuid', '=', 'add_scope.project_uuid')
                ->whereColumn('trx.original_uuid', '=', 'manpower_stds.uuid')
                ->where('projects.status', '!=', 'approve')
                ->selectRaw('COUNT(projects.uuid)'),
        ]);
    }
}
