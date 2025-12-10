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

    public function scopeDoestHaveTransaction(Builder $builder, ?string $inspectionType = null)
    {
        $builder->when($inspectionType, function ($query) use ($inspectionType) {
            $query
                ->has('inspectionType')
                ->whereNotExists(function ($sub) use ($inspectionType) {
                    $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                    $sub->selectRaw(1)
                        ->from("{$trxDb}.additional_scopes as trx")
                        ->leftJoin("{$trxDb}.projects as p", "p.uuid", "=", "trx.project_uuid")
                        ->whereRaw('trx.original_uuid = additional_scopes.uuid')
                        ->where("p.inspection_type_uuid", "=", $inspectionType)
                        ->where("p.uuid", "=", request()->input('project_uuid', null));
                })
                ->where('inspection_type_uuid', '=', $inspectionType);
        });
    }

    public function scopeHasTransaction(Builder $builder)
    {
        $databaseName = DatabaseConnectionEnum::TRANSACTION->value;
        $builder->addSelect([
            'has_transaction' => DB::
                table("{$databaseName}.additional_scopes as trx")
                ->leftJoin("{$databaseName}.projects", 'projects.uuid', '=', 'trx.project_uuid')
                ->whereColumn('trx.original_uuid', '=', 'additional_scopes.uuid')
                ->where('projects.status', '!=', 'approve')
                ->selectRaw('COUNT(projects.uuid)'),
        ]);
    }
}
