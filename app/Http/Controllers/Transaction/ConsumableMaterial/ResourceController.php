<?php

namespace App\Http\Controllers\Transaction\ConsumableMaterial;

use App\Core\Transaction\ConsumableMaterialStdCore;
use App\Data\PaginationData;
use App\Data\WhereOptionData;
use App\Enums\ConnectionEnum;
use App\Http\Requests\Transaction\CloneConsMatRequest;
use App\Http\Resources\PaginationResource;
use App\Models\ConsMatStd;
use App\Models\Transaction\Activity;
use App\Models\Transaction\ConsMat;
use App\Services\GenerateService;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Transaction Consumable Material Resource')]
class ResourceController extends ConsumableMaterialStdCore
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
    public function clone(CloneConsMatRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate consumable material
            $this->generateService->cloneConsumableMaterial(new WhereOptionData(
                'uuid',
                '=',
                $request->cons_mat_uuid,
                [
                    'activity_uuid' => $request->activity_uuid
                ]
            ));
        });

        return [
            'message' => 'Clone running successfully',
        ];
    }

    /**
     * list data by grouping data
     */
    #[Route(method: 'get', uri: 'grouping')]
    public function grouping(Request $request)
    {
        $pagination = new PaginationData($request);
        $query = ConsMat::query()
            ->select([
                'name',
                'merk',
                'unit',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(price) as price'),
                DB::raw('GROUP_CONCAT(uuid separator ";") as uuid')
            ])
            ->with($this->with)
            ->groupBy('name', 'merk', 'unit');

        $pagination = $query->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        // Ambil data untuk summary (pakai clone supaya query asli tidak terganggu)
        $summaryQuery = clone $query;
        $summaryCollection = $summaryQuery->get();

        return PaginationResource::collection($pagination)->additional([
            'summary' => [
                'total_qty' => $summaryCollection->sum('total_qty'),
                'total_price' => $summaryCollection->sum(function ($item) {
                    return optional($item->consmat)->price * $item->total_qty;
                }),
                'price' => $summaryCollection->sum(function ($item) {
                    return optional($item->consmat)->price;
                })
            ],
        ]);
    }

    /**
     * data for options select
     */
    #[Route(method: 'get', uri: 'select/options')]
    public function select(Request $request)
    {
        $pagination = new PaginationData($request);

        $trxActivity = Activity::where('uuid', $request->get('activity_uuid'))->first();
        $equipment = ConsMatStd::query()
            ->with(['consmat'])
            ->doestHaveTransaction($request->input('inspection_type_uuid', null), $request->input('activity_uuid', null))
            ->when($trxActivity, fn($query) => $query->where('activity_uuid', '=', $trxActivity->original_uuid))
            ->when($request->filled('project_uuid'), fn($query) => $query->whereHas('activity.equipment.scopeStandart', fn($scope) => $scope->doesntHave('additionalScope')))
            ->when($request->filled('additional_scope'), fn($query) => $query->whereHas('activity.equipment.scopeStandart', fn($scope) => $scope->doesntHave('inspectionType')))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $equipment;
    }
}
