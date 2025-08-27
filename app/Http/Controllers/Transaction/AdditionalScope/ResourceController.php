<?php

namespace App\Http\Controllers\Transaction\AdditionalScope;

use App\Enums\AuthPermissionEnum;
use App\Enums\ConnectionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdditionalScopeRequest;
use App\Http\Requests\ScopeStandartAdditionalRequest;
use App\Models\Activity;
use App\Models\AdditionalScope as ModelsAdditionalScope;
use App\Models\ConsMatStd;
use App\Models\Equipment;
use App\Models\ManpowerStd;
use App\Models\PartStd;
use App\Models\ScopeStandart;
use App\Models\Transaction\AdditionalScope;
use App\Models\Transaction\ScopeStandartAsset;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Additional Scope Resources')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination;

    protected $model = AdditionalScope::class;
    protected array $search = [];
    protected array $with = ['assetWelnes.document', 'ohRecom.document', 'woPriority.document', 'history.document', 'rla.document', 'ncr.document'];

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['pagination']),
        ];
    }

    /**
     * store asset
     */
    #[Route('POST')]
    public function asset(ScopeStandartAdditionalRequest $request)
    {
        $exist = ScopeStandartAsset::where('scope_standart_uuid', $request->scope_standart_uuid)->where('category', $request->category)->first();

        if ($exist) {
            ScopeStandartAsset::where('scope_standart_uuid', $request->scope_standart_uuid)
                ->where('category', $request->category)
                ->update($request->all());
            return $exist;
        }
        return ScopeStandartAsset::create($request->all());
    }

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(AdditionalScopeRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate additional scope
            ModelsAdditionalScope::select('uuid', 'name', 'sequence_uuid')
                ->where('uuid', $request->additional_scope_uuid)
                ->each(function ($addScope) use ($request) {
                    $duplicate = $addScope->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('additional_scopes');
                    $duplicate->project_uuid = $request->project_uuid;
                    $duplicate->save();

                    // duplicate scope standart
                    ScopeStandart::select('uuid', 'name', 'link', 'category', 'sub_bidang_uuid')
                        ->where('additional_scope_uuid', $addScope->uuid)
                        ->each(function ($row) use ($duplicate) {
                        $duplicate = $row->replicate();
                        $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                        $duplicate->setTable('scope_standarts');
                        $duplicate->additional_scope_uuid = $duplicate->uuid;
                        $duplicate->save();

                        // duplicate equipment
                        Equipment::select('uuid', 'scope_standart_uuid', 'name', 'link_ik1', 'link_ik2')
                            ->whereHas('scopeStandart', fn($query) => $query->where('scope_standart_uuid', $row->uuid))
                            ->each(function ($equipment) use ($duplicate) {
                            $duplicate = $equipment->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('equipment');
                            $duplicate->scope_standart_uuid = $duplicate->uuid;
                            $duplicate->save();

                            // duplicate activity
                            Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                                ->whereHas('equipment.scopeStandart', fn($query) => $query->where('equipment_uuid', $equipment->uuid))
                                ->each(function ($activity) use ($duplicate) {
                                $duplicate = $activity->replicate();
                                $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                $duplicate->setTable('activities');
                                $duplicate->equipment_uuid = $duplicate->uuid;
                                $duplicate->save();

                                // duplicate consumable material
                                ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                    ->each(function ($row) use ($duplicate) {
                                    $duplicate = $row->replicate();
                                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                    $duplicate->setTable('cons_mat_stds');
                                    $duplicate->activity_uuid = $duplicate->uuid;
                                    $duplicate->save();
                                });

                                // duplicate part std
                                PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                    ->each(function ($row) use ($duplicate) {
                                    $duplicate = $row->replicate();
                                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                    $duplicate->setTable('part_stds');
                                    $duplicate->activity_uuid = $duplicate->uuid;
                                    $duplicate->save();
                                });

                                // duplicate manpower std
                                ManpowerStd::select('uuid', 'activity_uuid', 'manpower_uuid', 'qty')
                                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                    ->each(function ($row) use ($duplicate) {
                                    $duplicate = $row->replicate();
                                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                    $duplicate->setTable('manpower_stds');
                                    $duplicate->activity_uuid = $duplicate->uuid;
                                    $duplicate->save();
                                });
                            });
                        });
                    });
                });
        });

        return [
            'message' => 'Clone running successfully',
        ];
    }
}
