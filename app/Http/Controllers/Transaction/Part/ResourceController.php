<?php

namespace App\Http\Controllers\Transaction\Part;

use App\Core\Transaction\PartStdCore;
use App\Data\PaginationData;
use App\Data\WhereOptionData;
use App\Enums\ConnectionEnum;
use App\Http\Requests\Transaction\ClonePartRequest;
use App\Http\Resources\PaginationResource;
use App\Models\PartStd;
use App\Models\Transaction\Activity;
use App\Models\Transaction\Part;
use App\Services\GenerateService;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Part Resource')]
class ResourceController extends PartStdCore
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
    public function clone(ClonePartRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate part std
            $this->generateService->clonePart(new WhereOptionData(
                'uuid',
                '=',
                $request->part_uuid,
                [
                    'activity_uuid' => $request->activity_uuid
                ]
            ));
            PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                ->where('uuid', $request->part_uuid)
                ->each(function ($row) use ($request) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('part_stds');
                    $duplicate->activity_uuid = $request->activity_uuid;
                    $duplicate->original_uuid = $row->uuid;
                    $duplicate->save();
                });
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
        $query = Part::query()
            ->select([
                'name',
                'merk',
                'no_drawing',
                'unit',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(price) as price'),
                DB::raw('GROUP_CONCAT(uuid separator ";") as uuid')
            ])
            ->with($this->with())
            ->groupBy('name', 'merk', 'no_drawing', 'unit');

        $pagination = $query->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        // Ambil data untuk summary (pakai clone supaya query asli tidak terganggu)
        $summaryQuery = clone $query;
        $summaryCollection = $summaryQuery->get();

        return PaginationResource::collection($pagination)->additional([
            'summary' => [
                'total_qty' => $summaryCollection->sum('total_qty'),
                'total_price' => $summaryCollection->sum(function ($item) {
                    return optional($item->part)->price * $item->total_qty;
                }),
                'price' => $summaryCollection->sum(function ($item) {
                    return optional($item->part)->price;
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
        $equipment = PartStd::query()
            ->with(['part'])
            ->doestHaveTransaction($request->input('inspection_type_uuid', null), $request->input('activity_uuid', null))
            ->when($trxActivity, fn($query) => $query->where('activity_uuid', '=', $trxActivity->original_uuid))
            ->when($request->filled('project_uuid'), fn($query) => $query->whereHas('activity.equipment.scopeStandart', fn($scope) => $scope->doesntHave('additionalScope')))
            ->when($request->filled('additional_scope'), fn($query) => $query->whereHas('activity.equipment.scopeStandart', fn($scope) => $scope->doesntHave('inspectionType')))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $equipment;
    }
}
