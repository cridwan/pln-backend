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
 */
#[ObservedBy([UppercaseObservser::class])]
class Part extends Model
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
            \Log::info($activity);
            $query->whereNotExists(function ($subQuery) use ($activity) {
                $subQuery->select(DB::raw(1))
                    ->from('part_stds')
                    ->whereRaw('parts.uuid = part_stds.part_uuid')
                    ->where('part_stds.activity_uuid', '=', $activity);
            });
        });
    }
}
