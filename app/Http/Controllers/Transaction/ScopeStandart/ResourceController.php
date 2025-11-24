<?php

namespace App\Http\Controllers\Transaction\ScopeStandart;

use App\Core\Transaction\ScopeStandartCore;
use App\Data\PaginationData;
use App\Enums\ConnectionEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\ScopeStandartRequest;
use App\Http\Requests\Transaction\CloneScopeRequest;
use App\Models\ConsMatStd;
use App\Models\Equipment;
use App\Models\ManpowerStd;
use App\Models\PartStd;
use App\Models\ScopeStandart as ModelsScopeStandart;
use App\Models\Transaction\Activity;
use App\Models\Transaction\ScopeStandart;
use App\Models\Transaction\ScopeStandartAsset;
use App\Services\GenerateService;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Transaction Scope Standart Resource')]
class ResourceController extends ScopeStandartCore
{
    use InitCore;
    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }

    /**
     * store asset
     */
    #[Route('POST')]
    public function asset(ScopeStandartRequest $request)
    {
        $exist = ScopeStandartAsset::where('scope_standart_uuid', $request->scope_standart_uuid)
            ->where('category', $request->category)
            ->first();

        if ($exist) {
            ScopeStandartAsset::where('scope_standart_uuid', $request->scope_standart_uuid)
                ->where('category', $request->category)
                ->update($request->all());
            return $exist;
        }
        return ScopeStandartAsset::create($request->all());
    }

    /**
     * Summary of total days
     * @return array
     */
    #[Route(method: 'GET')]
    public function duration(Request $request)
    {
        $total = Activity::query()
            ->when(
                $request->filled('project_uuid') && $request->project_uuid != 'undefined',
                function ($subQuery) use ($request) {
                    $subQuery->whereHas(
                        'equipment.scopeStandart',
                        fn($query) => $query->where('project_uuid', $request->project_uuid)
                    );
                }
            )->sum('duration');

        return ['data' => $total];
    }

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(CloneScopeRequest $request)
    {
        $scopeStandart = ScopeStandart::where('uuid', $request->scope_standart_uuid)->first();

        if ($scopeStandart) {
            throw new BadRequestException('Data ' . $scopeStandart->name . ' sudah dilakukan cloning');
        }

        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            ModelsScopeStandart::select('uuid', 'name', 'link', 'sub_bidang_uuid')
                ->where('uuid', $request->scope_standart_uuid)
                ->each(function ($scope) use ($request) {
                    $duplicateScope = $scope->replicate();
                    $duplicateScope->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicateScope->setTable('scope_standarts');
                    $duplicateScope->original_uuid = $scope->uuid;

                    // clone document
                    GenerateService::make()->cloneDocument(ModelsScopeStandart::class, $scope->uuid, "App\\Models\\Transaction\\ScopeStandart", $duplicateScope->uuid);

                    if ($request->filled('project_uuid')) {
                        $duplicateScope->project_uuid = $request->project_uuid;
                    }

                    if ($request->filled('additional_scope_uuid')) {
                        $duplicateScope->additional_scope_uuid = $request->additional_scope_uuid;
                    }

                    $duplicateScope->save();

                    // duplicate equipment
                    Equipment::select('uuid', 'scope_standart_uuid', 'name', 'link_ik1', 'link_ik2')
                        ->whereHas('scopeStandart', fn($query) => $query->where('scope_standart_uuid', $scope->uuid))
                        ->each(function ($equipment) use ($duplicateScope) {
                        $duplicateEquipment = $equipment->replicate();
                        $duplicateEquipment->setConnection(ConnectionEnum::TRANSACTION->value);
                        $duplicateEquipment->setTable('equipment');
                        $duplicateEquipment->scope_standart_uuid = $duplicateScope->uuid;
                        $duplicateEquipment->original_uuid = $equipment->uuid;
                        $duplicateEquipment->save();

                        // duplicate activity
                        Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                            ->whereHas('equipment.scopeStandart', fn($query) => $query->where('equipment_uuid', $equipment->uuid))
                            ->each(function ($activity) use ($duplicateEquipment) {
                            $duplicateActivity = $activity->replicate();
                            $duplicateActivity->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicateActivity->setTable('activities');
                            $duplicateActivity->equipment_uuid = $duplicateEquipment->uuid;
                            $duplicateActivity->original_uuid = $activity->uuid;
                            $duplicateActivity->save();

                            // clone document
                            GenerateService::make()->cloneDocument(Activity::class, $activity->uuid, "App\\Models\\Transaction\\Activity", $duplicateActivity->uuid);

                            // duplicate consumable material
                            ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                                ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                ->each(function ($row) use ($duplicateActivity, $activity) {
                                $duplicate = $row->replicate();
                                $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                $duplicate->setTable('cons_mat_stds');
                                $duplicate->activity_uuid = $duplicateActivity->uuid;
                                $duplicateActivity->original_uuid = $activity->uuid;
                                $duplicate->save();
                            });

                            // duplicate part std
                            PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                                ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                ->each(function ($row) use ($duplicateActivity, $activity) {
                                $duplicate = $row->replicate();
                                $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                $duplicate->setTable('part_stds');
                                $duplicate->activity_uuid = $duplicateActivity->uuid;
                                $duplicateActivity->original_uuid = $activity->uuid;
                                $duplicate->save();
                            });

                            // duplicate manpower std
                            ManpowerStd::select('uuid', 'activity_uuid', 'manpower_uuid', 'qty')
                                ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                                ->each(function ($row) use ($duplicateActivity, $activity) {
                                $duplicate = $row->replicate();
                                $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                                $duplicate->setTable('manpower_stds');
                                $duplicate->activity_uuid = $duplicateActivity->uuid;
                                $duplicateActivity->original_uuid = $activity->uuid;
                                $duplicate->save();
                            });
                        });
                    });
                });
        });

        return [
            'message' => 'Clone running successfully',
        ];
    }

    /**
     * data for options select
     */
    #[Route(method: 'get', uri: 'select/options')]
    public function select(Request $request)
    {
        $pagination = new PaginationData($request);

        $scopes = ModelsScopeStandart::query()
            ->doestHaveTransaction($request->input('inspection_type_uuid', null))
            ->when($request->filled('sub_bidang_uuid'), fn($scope) => $scope->where('sub_bidang_uuid', $request->sub_bidang_uuid))
            ->when($request->filled('project_uuid'), fn($query) => $query->doesntHave('additionalScope'))
            ->when($request->filled('additional_scope_uuid'), fn($query) => $query->doesntHave('inspectionType'))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $scopes;
    }
}
