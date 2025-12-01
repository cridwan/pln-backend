<?php

namespace App\Models;

use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use DB;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method \Illuminate\Database\Eloquent\Builder<static>  doesntHaveStd(?string $activity = null)
 * @method \Illuminate\Database\Eloquent\Builder<static>  hasTransaction()
 */
#[ObservedBy([UppercaseObservser::class])]
class Manpower extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';

    public function globalUnit()
    {
        return $this->belongsTo(GlobalUnit::class, 'global_unit_uuid');
    }

    public function scopeDoesntHaveStd(Builder $query, ?string $activity = null)
    {
        $query->when($activity, function ($query) use ($activity) {
            $query->whereNotExists(function ($subQuery) use ($activity) {
                $subQuery->select(DB::raw(1))
                    ->from('manpower_stds')
                    ->whereRaw('manpowers.uuid = manpower_stds.manpower_uuid')
                    ->where('manpower_stds.activity_uuid', '=', $activity);
            });
        });
    }

    public function stds()
    {
        return $this->hasMany(ManpowerStd::class, 'manpower_uuid');
    }

    public function scopeHasTransaction(Builder $builder)
    {
        $builder->addSelect([
            'has_transaction' => DB::table('manpower_stds')
                ->whereColumn('manpower_stds.manpower_uuid', '=', 'manpowers.uuid')
                ->selectRaw('COUNT(manpower_stds.uuid)'),
        ]);
    }
}
