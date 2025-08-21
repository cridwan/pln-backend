<?php

namespace App\Http\Controllers\Transaction\ScopeStandart;

use App\Enums\AuthPermissionEnum;
use App\Enums\ConnectionEnum;
use App\Enums\RoleEnum;
use App\Enums\ScopeStandartTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
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
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Transaction Scope Standart Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination, HasApiResource;

    protected $model = ScopeStandart::class;
    protected array $search = ['name'];
    protected array $with = ['document', 'assetWelnes.document', 'ohRecom.document', 'woPriority.document', 'history.document', 'rla.document', 'ncr.document'];
    protected $rules = [];

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['pagination']),
            new Middleware(
                RoleMiddleware::using(
                    [RoleEnum::PLANNER],
                ),
                only: ['clone']
            )
        ];
    }

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => 'required',
            'category' => ['required', Rule::enum(ScopeStandartTypeEnum::class)],
            'project_uuid' => 'nullable',
            'additional_scope_uuid' => 'nullable'
        ];
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
        $total =  Activity::when(
            $request->filled('project_uuid'),
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
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            ModelsScopeStandart::select('uuid', 'name', 'link', 'category', 'sub_bidang_uuid')
                ->where('uuid', $request->scope_standart_uuid)
                ->each(function ($scope) use ($request) {
                    $duplicate = $scope->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('scope_standarts');
                    $duplicate->project_uuid = $request->project_uuid;
                    $duplicate->save();

                    // duplicate equipment
                    Equipment::select('uuid', 'scope_standart_uuid', 'name', 'link_ik1', 'link_ik2')
                        ->whereHas('scopeStandart', fn($query) => $query->where('scope_standart_uuid', $scope->uuid))
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

        return [
            'message' => 'Clone running successfully',
        ];
    }
}
