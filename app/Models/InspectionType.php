<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ObservedBy([UppercaseObservser::class])]
/**
 * @method \Illuminate\Database\Eloquent\Builder<static>  hasTransaction()
 */
class InspectionType extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine_uuid');
    }

    public function sequence()
    {
        return $this->belongsTo(Sequence::class, 'sequence_uuid');
    }

    public function scopeHasTransaction(Builder $builder)
    {
        $builder->addSelect([
            'has_transaction' => DB::connection(ConnectionEnum::TRANSACTION->value)
                ->table('projects')
                ->whereColumn('projects.inspection_type_uuid', '=', 'inspection_types.uuid')
                ->where('projects.status', '!=', 'approve')
                ->selectRaw('COUNT(projects.uuid)'),
        ]);
    }
}
