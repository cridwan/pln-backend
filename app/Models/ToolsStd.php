<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Enums\DatabaseConnectionEnum;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ObservedBy([UppercaseObservser::class])]
class ToolsStd extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function tool()
    {
        return $this->belongsTo(Tools::class, 'tools_uuid', 'uuid');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_uuid', 'uuid');
    }

    public function scopeHasTransaction(Builder $builder)
    {
        $databaseName = DatabaseConnectionEnum::TRANSACTION->value;
        $builder->addSelect([
            'has_transaction' => DB::table("{$databaseName}.tools_stds as trx")
                ->leftJoin("{$databaseName}.activities as ac", 'ac.uuid', '=', 'trx.activity_uuid')
                ->leftJoin("{$databaseName}.equipment as eq", 'eq.uuid', '=', 'ac.equipment_uuid')
                ->leftJoin("{$databaseName}.scope_standarts as scope", 'scope.uuid', '=', 'eq.scope_standart_uuid')
                ->leftJoin("{$databaseName}.projects", 'projects.uuid', '=', 'scope.project_uuid')
                ->whereColumn('trx.original_uuid', '=', 'tools_stds.uuid')
                ->where('projects.status', '!=', 'approve')
                ->selectRaw('COUNT(projects.uuid)'),
        ]);
    }


    public function scopeHasTransactionDetail(Builder $builder)
    {
        $databaseName = DatabaseConnectionEnum::TRANSACTION->value;
        $builder->addSelect([
            'has_transaction' => DB::table("{$databaseName}.tools_stds as trx")
                ->leftJoin("{$databaseName}.activities as ac", 'ac.uuid', '=', 'trx.activity_uuid')
                ->leftJoin("{$databaseName}.equipment as eq", 'eq.uuid', '=', 'ac.equipment_uuid')
                ->leftJoin("{$databaseName}.scope_standarts as scope", 'scope.uuid', '=', 'eq.scope_standart_uuid')
                ->leftJoin("{$databaseName}.additional_scopes as add_scope", 'add_scope.uuid', '=', 'scope.additional_scope_uuid')
                ->leftJoin("{$databaseName}.projects", 'projects.uuid', '=', 'add_scope.project_uuid')
                ->whereColumn('trx.original_uuid', '=', 'tools_stds.uuid')
                ->where('projects.status', '!=', 'approve')
                ->selectRaw('COUNT(projects.uuid)'),
        ]);
    }


    public function scopeDoestHaveTransaction(Builder $builder, ?string $inspectionType = null, ?string $activity = null)
    {
        $builder->when($inspectionType, function ($query) use ($inspectionType, $activity) {
            $query
                ->has('activity.equipment.scopeStandart.inspectionType')
                ->whereNotExists(function ($sub) use ($inspectionType) {
                    $trxDb = DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                    $sub->selectRaw(1)
                        ->from("{$trxDb}.tools_stds as trx")
                        ->leftJoin("{$trxDb}.activities as ac", "ac.uuid", "=", "trx.activity_uuid")
                        ->leftJoin("{$trxDb}.equipment as eq", "eq.uuid", "=", "ac.equipment_uuid")
                        ->leftJoin("{$trxDb}.scope_standarts as scope", "scope.uuid", "=", "eq.scope_standart_uuid")
                        ->leftJoin("{$trxDb}.projects as p", "p.uuid", "=", "scope.project_uuid")
                        ->whereRaw('trx.original_uuid = tools_stds.uuid')
                        ->where("p.inspection_type_uuid", "=", $inspectionType)
                        ->where("p.uuid", "=", request()->input('project_uuid', null));
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
                        ->from("{$trxDb}.tools_stds as trx")
                        ->leftJoin("{$trxDb}.activities as ac", "ac.uuid", "=", "trx.activity_uuid")
                        ->leftJoin("{$trxDb}.equipment as eq", "eq.uuid", "=", "ac.equipment_uuid")
                        ->leftJoin("{$trxDb}.scope_standarts as scope", "scope.uuid", "=", "eq.scope_standart_uuid")
                        ->leftJoin("{$trxDb}.additional_scopes as ad_scope", "ad_scope.uuid", "=", "scope.additional_scope_uuid")
                        ->leftJoin("{$trxDb}.projects as p", "ad_scope.project_uuid", "=", "p.uuid")
                        ->whereRaw('trx.original_uuid = tools_stds.uuid')
                        ->where("ad_scope.original_uuid", "=", $additionalScope)
                        ->where("p.uuid", "=", request()->input("project_uuid", null));
                })
                ->whereHas('activity.equipment.scopeStandart', function ($where) use ($additionalScope) {
                    $where->where('additional_scope_uuid', '=', $additionalScope);
                })
                ->when(request()->input('activity_uuid', null), function ($query) {
                    $query->where('activity_uuid', '=', request()->input('activity_uuid'));
                });
        });
    }
}
