<?php

namespace App\Http\AdditionalControllers\Transaction\Activity;

use App\Core\Transaction\Detail\ActivityCore;
use App\Data\PaginationData;
use App\Data\WhereOptionData;
use App\Enums\ConnectionEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\Transaction\CloneActivityRequest;
use App\Models\Activity;
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
    public function __construct(public GenerateService $generateService)
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
            $this->generateService->cloneActivity(new WhereOptionData(
                'uuid',
                '=',
                $request->activity_uuid,
                [
                    'equipment_uuid' => $request->equipment_uuid
                ]
            ));
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

        $equipment = Activity::query()
            ->doestHaveTransactionDetail($request->input('additional_scope_uuid', null))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $equipment;
    }
}
