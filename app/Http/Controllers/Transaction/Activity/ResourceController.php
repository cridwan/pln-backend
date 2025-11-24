<?php

namespace App\Http\Controllers\Transaction\Activity;

use App\Core\Transaction\ActivityCore;
use App\Data\PaginationData;
use App\Enums\ConnectionEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\Transaction\CloneActivityRequest;
use App\Models\Activity;
use App\Models\ConsMatStd;
use App\Models\ManpowerStd;
use App\Models\PartStd;
use App\Models\Transaction\Activity as TransactionActivity;
use App\Models\Transaction\Equipment;
use App\Services\GenerateService;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Transaction Activity Resource')]
class ResourceController extends ActivityCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(CloneActivityRequest $request)
    {
        $activity = TransactionActivity::where('uuid', $request->activity_uuid)->first();
        if ($activity) {
            throw new BadRequestException('Data ' . $activity->name . ' sudah di cloning');
        }

        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate activity
            Activity::select('uuid', 'equipment_uuid', 'name', 'duration', 'link_ik1', 'link_ik2')
                ->where('uuid', $request->activity_uuid)
                ->each(function ($activity) use ($request) {
                    $duplicateActivity = $activity->replicate();
                    $duplicateActivity->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicateActivity->setTable('activities');
                    $duplicateActivity->uuid = $activity->uuid;
                    $duplicateActivity->equipment_uuid = $request->equipment_uuid;
                    $duplicateActivity->original_uuid = $activity->uuid;
                    $duplicateActivity->save();

                    // clone document
                    GenerateService::make()->cloneDocument(Activity::class, $activity->uuid, "App\\Models\\Transaction\\Activity", $duplicateActivity->uuid);

                    // duplicate consumable material
                    ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                        ->whereHas('activity.equipment.scopeStandart', fn($query) => $query->where('activity_uuid', $activity->uuid))
                        ->each(function ($row) use ($duplicateActivity) {
                        $duplicate = $row->replicate();
                        $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                        $duplicate->setTable('cons_mat_stds');
                        $duplicate->activity_uuid = $duplicateActivity->uuid;
                        $duplicate->original_uuid = $row->uuid;
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
                        $duplicate->original_uuid = $row->uuid;
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
                        $duplicate->original_uuid = $row->uuid;
                        $duplicate->save();
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

        $trxEquipment = Equipment::where('uuid', $request->get('equipment_uuid'))->first();
        $equipment = Activity::query()
            ->doestHaveTransaction($request->input('inspection_type_uuid', null), $request->input('equipment_uuid', null))
            ->when($trxEquipment, fn($query) => $query->where('equipment_uuid', '=', $trxEquipment->original_uuid))
            ->when($request->filled('project_uuid'), fn($query) => $query->whereHas('equipment.scopeStandart', fn($scope) => $scope->doesntHave('additionalScope')))
            ->when($request->filled('additional_scope'), fn($query) => $query->whereHas('equipment.scopeStandart', fn($scope) => $scope->doesntHave('inspectionType')))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $equipment;
    }
}
