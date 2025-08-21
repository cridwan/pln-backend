<?php

namespace App\Http\Controllers\Transaction\Activity;

use App\Enums\AuthPermissionEnum;
use App\Enums\ConnectionEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Requests\Transaction\CloneActivityRequest;
use App\Models\Activity;
use App\Models\ConsMatStd;
use App\Models\ManpowerStd;
use App\Models\PartStd;
use App\Models\Transaction\Activity as TransactionActivity;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Transaction Activity Resource')]
class ResourceController extends Controller
{
    use HasPagination, HasApiResource;

    protected $model = TransactionActivity::class;
    protected array $search = ['name'];
    protected array $with = ['activity'];
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

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(CloneActivityRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate activity
            Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                ->where('uuid', $request->activity_uuid)
                ->each(function ($activity) use ($request) {
                    $duplicate = $activity->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('activities');
                    $duplicate->equipment_uuid = $request->equipment_uuid;
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

        return [
            'message' => 'Clone running successfully',
        ];
    }
}
