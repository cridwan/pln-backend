<?php

namespace App\Http\Controllers\Transaction\AdditionalScope;

use App\Core\Transaction\AdditionalScopeCore;
use App\Enums\ConnectionEnum;
use App\Http\Requests\ScopeStandartAdditionalRequest;
use App\Http\Requests\Transaction\CloneAdditionalScopeRequest;
use App\Models\Activity;
use App\Models\AdditionalScope as ModelsAdditionalScope;
use App\Models\ConsMatStd;
use App\Models\Equipment;
use App\Models\ManpowerStd;
use App\Models\PartStd;
use App\Models\ScopeStandart;
use App\Models\Transaction\ScopeStandartAsset;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Additional Scope Resources')]
class ResourceController extends AdditionalScopeCore
{
    use InitCore;

    public function __construct()
    {
        $this->initCore();
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
    public function clone(CloneAdditionalScopeRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate additional scope
            ModelsAdditionalScope::select('uuid', 'name', 'sequence_uuid')
                ->where('uuid', $request->additional_scope_uuid)
                ->each(function ($addScope) use ($request) {
                    $duplicateAdd = $addScope->replicate();
                    $duplicateAdd->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicateAdd->setTable('additional_scopes');
                    $duplicateAdd->project_uuid = $request->project_uuid;
                    $duplicateAdd->original_uuid = $addScope->uuid;
                    $duplicateAdd->save();

                    // duplicate scope standart
                    ScopeStandart::select('uuid', 'name', 'link', 'category', 'sub_bidang_uuid')
                        ->where('additional_scope_uuid', $addScope->uuid)
                        ->each(function ($scope) use ($duplicateAdd) {
                        $duplicateScope = $scope->replicate();
                        $duplicateScope->setConnection(ConnectionEnum::TRANSACTION->value);
                        $duplicateScope->setTable('scope_standarts');
                        $duplicateScope->additional_scope_uuid = $duplicateAdd->uuid;
                        $duplicateScope->original_uuid = $scope->uuid;
                        $duplicateScope->save();

                        // duplicate equipment
                        Equipment::select('uuid', 'scope_standart_uuid', 'name', 'link_ik1', 'link_ik2')
                            ->whereHas('scopeStandart', fn($query) => $query->where('scope_standart_uuid', $scope->uuid))
                            ->each(function ($equipment) use ($duplicateScope) {
                            $duplicateEq = $equipment->replicate();
                            $duplicateEq->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicateEq->setTable('equipment');
                            $duplicateEq->scope_standart_uuid = $duplicateScope->uuid;
                            $duplicateEq->original_uuid = $equipment->uuid;
                            $duplicateEq->save();

                            // duplicate activity
                            Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                                ->whereHas('equipment.scopeStandart', fn($query) => $query->where('equipment_uuid', $equipment->uuid))
                                ->each(function ($activity) use ($duplicateEq) {
                                $duplicateAc = $activity->replicate();
                                $duplicateAc->setConnection(ConnectionEnum::TRANSACTION->value);
                                $duplicateAc->setTable('activities');
                                $duplicateAc->equipment_uuid = $duplicateEq->uuid;
                                $duplicateAc->original_uuid = $activity->uuid;
                                $duplicateAc->save();

                                // duplicate consumable material
                                ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                    ->each(function ($row) use ($duplicateAc) {
                                    $duplicate = $row->replicate();
                                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                    $duplicate->setTable('cons_mat_stds');
                                    $duplicate->activity_uuid = $duplicateAc->uuid;
                                    $duplicate->original_uuid = $row->uuid;
                                    $duplicate->save();
                                });

                                // duplicate part std
                                PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                    ->each(function ($row) use ($duplicateAc) {
                                    $duplicate = $row->replicate();
                                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                    $duplicate->setTable('part_stds');
                                    $duplicate->activity_uuid = $duplicateAc->uuid;
                                    $duplicate->original_uuid = $row->uuid;
                                    $duplicate->save();
                                });

                                // duplicate manpower std
                                ManpowerStd::select('uuid', 'activity_uuid', 'manpower_uuid', 'qty')
                                    ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                    ->each(function ($row) use ($duplicateAc) {
                                    $duplicate = $row->replicate();
                                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                    $duplicate->setTable('manpower_stds');
                                    $duplicate->activity_uuid = $duplicateAc->uuid;
                                    $duplicate->original_uuid = $row->uuid;
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
