<?php

namespace App\Models;

use App\Enums\ConnectionEnum;
use App\Observers\HseDocObserver;
use App\Observers\UppercaseObservser;
use App\Traits\SettingModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([UppercaseObservser::class, HseDocObserver::class])]
/**
 * @method \Illuminate\Database\Eloquent\Builder<static> doestHaveTransaction(?string $project)
 */
class HseDoc extends Model
{
    use SettingModel;

    protected $connection = 'masterdata';


    public function scopeDoestHaveTransaction(Builder $builder, ?string $project = null)
    {
        $builder->when($project, function ($query) use ($project) {
            $query
                ->whereNotExists(function ($sub) use ($project) {
                    $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                    $sub->selectRaw(1)
                        ->from("{$trxDb}.hse_docs as trx")
                        ->whereRaw('trx.original_uuid = hse_docs.uuid')
                        ->where("trx.project_uuid", "=", $project);
                });
        });
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction\HseDoc::class, 'original_uuid');
    }
}
