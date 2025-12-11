<?php

namespace App\Models;

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
}
