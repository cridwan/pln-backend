<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Models\Storage\Document;
use App\Observers\ActivityObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class, ActivityObserver::class])]
/**
 * @method \Illuminate\Database\Eloquent\Builder<static> doestHaveTransaction(?string $inspectionType = null, ?string $equipment = null)
 */
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

    public function scopeDoestHaveTransaction(Builder $builder, ?string $inspectionType = null, ?string $equipment = null)
    {
        $builder->when($inspectionType, function ($query) use ($inspectionType, $equipment) {
            $query
                ->has('equipment.scopeStandart.inspectionType')
                ->whereNotExists(function ($sub) use ($inspectionType) {
                    $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                    $sub->selectRaw(1)
                        ->from("{$trxDb}.activities as trx")
                        ->leftJoin("{$trxDb}.equipment as eq", "eq.uuid", "=", "trx.equipment_uuid")
                        ->leftJoin("{$trxDb}.scope_standarts as scope", "scope.uuid", "=", "eq.scope_standart_uuid")
                        ->leftJoin("{$trxDb}.projects as p", "p.uuid", "=", "scope.project_uuid")
                        ->whereRaw('trx.original_uuid = activities.uuid')
                        ->where("p.inspection_type_uuid", "=", $inspectionType);
                })
                ->whereHas('equipment.scopeStandart', function ($where) use ($inspectionType) {
                    $where->where('inspection_type_uuid', '=', $inspectionType);
                })
                ->when($equipment, function ($where) use ($equipment) {
                    $where->where('equipment_uuid', '=', $equipment);
                });
        });
    }

    public function generateSerialNumber()
    {
        return $this->where('equipment_uuid', '=', $this->equipment_uuid)->count() + 1;
    }

    public function manpowers()
    {
        return $this->hasMany(ManpowerStd::class);
    }

    public function materials()
    {
        return $this->hasMany(ConsMatStd::class);
    }
    public function parts()
    {
        return $this->hasMany(PartStd::class);
    }
}
