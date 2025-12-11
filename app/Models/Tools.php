<?php

namespace App\Models;

use App\Enums\ToolStatusEnum;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[ObservedBy([UppercaseObservser::class])]
class Tools extends Model
{
    use SettingModel, HasFactory;

    protected $connection = 'masterdata';

    protected $casts = [
        // 'status' => ToolStatusEnum::class,
    ];

    public function globalUnit(): BelongsTo
    {
        return $this->belongsTo(GlobalUnit::class, 'global_unit_uuid');
    }


    public function scopeDoesntHaveStd(Builder $query, ?string $activity = null)
    {
        $query->when($activity, function ($query) use ($activity) {
            $query->whereNotExists(function ($subQuery) use ($activity) {
                $subQuery->select(DB::raw(1))
                    ->from('tools_stds')
                    ->whereRaw('tools.uuid = tools_stds.tools_uuid')
                    ->where('tools_stds.activity_uuid', '=', $activity);
            });
        });
    }

    public function scopeHasTransaction(Builder $builder)
    {
        $builder->addSelect([
            'has_transaction' => DB::table('tools_stds')
                ->whereColumn('tools_stds.tools_uuid', '=', 'tools.uuid')
                ->selectRaw('COUNT(tools_stds.uuid)'),
        ]);
    }
}
