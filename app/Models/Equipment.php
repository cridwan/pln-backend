<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Enums\DatabaseConnectionEnum;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use DB;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class])]
/**
 * @method \Illuminate\Database\Eloquent\Builder<static> doestHaveTransaction(?string $inspectionType = null, ?string $scopeStandart = null)
 */
class Equipment extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function scopeStandart()
    {
        return $this->belongsTo(ScopeStandart::class, 'scope_standart_uuid');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
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
                } else if (request()->filled('additional_scope_uuid')) {
                    $subQuery->where('ss.additional_scope_uuid', '=', request()->get('additional_scope_uuid'));
                }
            });

            if (request()->filled('from_add_scope')) {
                $builder
                    ->whereHas('scopeStandart', fn($query) => $query->where('additional_scope_uuid', '=', request()->get('original_uuid')))
                    ->doesntHave('scopeStandart.inspectionType');
            } else {
                $builder->doesntHave('scopeStandart.additionalScope');
            }
        }
    }

    public function scopeDoestHaveTransaction(Builder $builder, ?string $inspectionType = null, ?string $scopeStandart = null)
    {
        $builder->when($inspectionType, function ($query) use ($inspectionType, $scopeStandart) {
            $query
                ->has('scopeStandart.inspectionType')
                ->whereNotExists(function ($sub) use ($inspectionType) {
                    $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                    $sub->selectRaw(1)
                        ->from("{$trxDb}.equipment as trx")
                        ->leftJoin("{$trxDb}.scope_standarts as scope", "scope.uuid", "=", "trx.scope_standart_uuid")
                        ->leftJoin("{$trxDb}.projects as p", "p.uuid", "=", "scope.project_uuid")
                        ->whereRaw('trx.original_uuid = equipment.uuid')
                        ->where("p.inspection_type_uuid", "=", $inspectionType);
                })
                ->whereHas('scopeStandart', function ($where) use ($inspectionType) {
                    $where->where('inspection_type_uuid', '=', $inspectionType);
                })
                ->when($scopeStandart, function ($where) use ($scopeStandart) {
                    $where->where('scope_standart_uuid', '=', $scopeStandart);
                });
        });
    }

    public function scopeDoestHaveTransactionDetail(Builder $builder, ?string $additionalScope = null)
    {
        $builder->when($additionalScope, function ($query) use ($additionalScope) {
            $query
                ->has('scopeStandart.additionalScope')
                ->whereNotExists(function ($sub) use ($additionalScope) {
                    $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                    $sub->selectRaw(1)
                        ->from("{$trxDb}.equipment as trx")
                        ->leftJoin("{$trxDb}.scope_standarts as scope", "scope.uuid", "=", "trx.scope_standart_uuid")
                        ->leftJoin("{$trxDb}.additional_scopes as ad_scope", "ad_scope.uuid", "=", "scope.additional_scope_uuid")
                        ->whereRaw('trx.original_uuid = equipment.uuid')
                        ->where("ad_scope.original_uuid", "=", $additionalScope);
                })
                ->whereHas('scopeStandart', function ($where) use ($additionalScope) {
                    $where->where('additional_scope_uuid', '=', $additionalScope);
                })
                ->when(request()->input('scope_standart_uuid', null), function ($query) {
                    $query->where('scope_standart_uuid', '=', request()->input('scope_standart_uuid'));
                });
        });
    }

    public function scopeHasTransaction(Builder $builder)
    {
        $databaseName = DatabaseConnectionEnum::TRANSACTION->value;
        $builder->addSelect([
            'has_transaction' => DB::table("{$databaseName}.equipment as trx")
                ->leftJoin("{$databaseName}.scope_standarts as scope", 'scope.uuid', '=', 'trx.scope_standart_uuid')
                ->leftJoin("{$databaseName}.projects", 'projects.uuid', '=', 'scope.project_uuid')
                ->whereColumn('trx.original_uuid', '=', 'equipment.uuid')
                ->where('projects.status', '!=', 'approve')
                ->selectRaw('COUNT(projects.uuid)'),
        ]);
    }

    public function scopeHasTransactionDetail(Builder $builder)
    {
        $databaseName = DatabaseConnectionEnum::TRANSACTION->value;
        $builder->addSelect([
            'has_transaction' => DB::table("{$databaseName}.equipment as trx")
                ->leftJoin("{$databaseName}.scope_standarts as scope", 'scope.uuid', '=', 'trx.scope_standart_uuid')
                ->leftJoin("{$databaseName}.additional_scopes as add_scope", 'add_scope.uuid', '=', 'scope.additional_scope_uuid')
                ->leftJoin("{$databaseName}.projects", 'projects.uuid', '=', 'add_scope.project_uuid')
                ->whereColumn('trx.original_uuid', '=', 'equipment.uuid')
                ->where('projects.status', '!=', 'approve')
                ->selectRaw('COUNT(projects.uuid)'),
        ]);
    }
}
