<?php

namespace App\Http\Controllers\Transaction\Equipment;

use App\Data\PaginationData;
use App\Enums\AuthPermissionEnum;
use App\Enums\ConnectionEnum;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Requests\Transaction\CloneEquipmentRequest;
use App\Models\Activity;
use App\Models\ConsMatStd;
use App\Models\Equipment as ModelsEquipment;
use App\Models\ManpowerStd;
use App\Models\PartStd;
use App\Models\Transaction\Equipment;
use App\Models\Transaction\ScopeStandart;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Transaction Equipments Resource')]
class ResourceController extends Controller
{
    use HasPagination, HasApiResource;

    protected $model = Equipment::class;
    protected array $search = ['name'];
    protected array $with = [];
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
    public function clone(CloneEquipmentRequest $request)
    {
        $equipment = Equipment::where('uuid', $request->equipment_uuid)->first();

        if ($equipment) {
            throw new BadRequestException('Data ' . $equipment->name . ' sudah dilakukan cloning');
        }

        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate equipment
            ModelsEquipment::select('uuid', 'scope_standart_uuid', 'name', 'link_ik1', 'link_ik2')
                ->where('uuid', $request->equipment_uuid)
                ->each(function ($equipment) use ($request) {
                    $duplicateEquipment = $equipment->replicate();
                    $duplicateEquipment->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicateEquipment->setTable('equipment');
                    $duplicateEquipment->scope_standart_uuid = $request->scope_standart_uuid;
                    $duplicateEquipment->save();

                    // duplicate activity
                    Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                        ->whereHas('equipment.scopeStandart', fn($query) => $query->where('equipment_uuid', $equipment->uuid))
                        ->each(function ($activity) use ($duplicateEquipment) {
                        $duplicateActivity = $activity->replicate();
                        $duplicateActivity->setConnection(ConnectionEnum::TRANSACTION->value);
                        $duplicateActivity->setTable('activities');
                        $duplicateActivity->equipment_uuid = $duplicateEquipment->uuid;
                        $duplicateActivity->save();
                        // duplicate consumable material
                        ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                            ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                            ->each(function ($row) use ($duplicateActivity) {
                            $duplicate = $row->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('cons_mat_stds');
                            $duplicate->activity_uuid = $duplicateActivity->uuid;
                            $duplicate->save();
                        });

                        // duplicate part std
                        PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                            ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                            ->each(function ($row) use ($duplicateActivity) {
                            $duplicate = $row->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('part_stds');
                            $duplicate->activity_uuid = $duplicateActivity->uuid;
                            $duplicate->save();
                        });

                        // duplicate manpower std
                        ManpowerStd::select('uuid', 'activity_uuid', 'manpower_uuid', 'qty')
                            ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                            ->each(function ($row) use ($duplicateActivity) {
                            $duplicate = $row->replicate();
                            $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                            $duplicate->setTable('manpower_stds');
                            $duplicate->activity_uuid = $duplicateActivity->uuid;
                            $duplicate->save();
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

        $trxScope = ScopeStandart::where('uuid', $request->get('scope_standart_uuid'))->first();
        $equipment = ModelsEquipment::query()
            ->whereNotExists(function ($subQuery) use ($request) {
                $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.equipment as trx')
                    ->join($trxDb . '.scope_standarts as ss', 'ss.uuid', '=', 'trx.scope_standart_uuid')
                    ->whereColumn('trx.original_uuid', '=', 'equipment.uuid')
                    ->when($request->filled('project_uuid'), fn($query) => $query->where('ss.project_uuid', '=', $request->get('project_uuid')))
                    ->when($request->filled('additional_scope_uuid'), fn($query) => $query->where('ss.additional_scope_uuid', '=', $request->get('additional_scope_uuid')));
            })
            ->when($trxScope, fn($query) => $query->where('scope_standart_uuid', '=', $trxScope->original_uuid))
            ->when($request->filled('project_uuid'), fn($query) => $query->whereHas('scopeStandart', fn($scope) => $scope->doesntHave('additionalScope')))
            ->when($request->filled('additional_scope'), fn($query) => $query->whereHas('scopeStandart', fn($scope) => $scope->doesntHave('inspectionType')))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $equipment;
    }
}
