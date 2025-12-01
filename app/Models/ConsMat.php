<?php

namespace App\Models;

use App\Observers\ConsumableObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use DB;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method \Illuminate\Database\Eloquent\Builder<static>  doesntHaveStd(?string $activity = null)
 */
#[ObservedBy([UppercaseObservser::class, ConsumableObserver::class])]
class ConsMat extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    protected $table = 'const_mats';

    public function globalUnit()
    {
        return $this->belongsTo(GlobalUnit::class, 'global_unit_uuid');
    }

    public function scopeDoesntHaveStd(Builder $query, ?string $activity = null)
    {
        $query->when($activity, function ($query) use ($activity) {
            $query->whereNotExists(function ($subQuery) use ($activity) {
                $subQuery->select(DB::raw(1))
                    ->from('cons_mat_stds')
                    ->whereRaw('const_mats.uuid = cons_mat_stds.cons_mat_uuid')
                    ->where('cons_mat_stds.activity_uuid', '=', $activity);
            });
        });
    }

    public function stds()
    {
        return $this->hasMany(ConsMatStd::class, 'cons_mat_uuid');
    }

    public function scopeHasTransaction(Builder $builder)
    {
        $builder->addSelect([
            'has_transaction' => DB::table('cons_mat_stds')
                ->whereColumn('cons_mat_stds.cons_mat_uuid', '=', 'const_mats.uuid')
                ->selectRaw('COUNT(cons_mat_stds.uuid)'),
        ]);
    }
}
