<?php

namespace App\Http\AdditionalControllers\Transaction\ScopeStandart;

use App\Core\Transaction\Detail\ScopeStandartCore;
use App\Data\PaginationData;
use App\Data\WhereOptionData;
use App\Enums\ConnectionEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\ScopeStandartRequest;
use App\Http\Requests\Transaction\CloneScopeRequest;
use App\Http\Resources\ResponseResource;
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
    public function __construct(public GenerateService $generateService)
    {
        $this->initCore();
    }

    /**
     * list data
     */
    #[Route(method: 'get')]
    public function pagination(Request $request)
    {
        $perPage = $request->filled('perPage') ? $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? $request->currentPage : 1;

        $query = ScopeStandart::query()
            ->has('additionalScope');
        $query->with($this->with());
        $query->when($request->filled('search'), callback: function ($subQuery) use ($request) {
            $subQuery->where(function ($search) use ($request) {
                $search->where('name', 'like', "%$request->search%");
            });
        });

        $query->when($request->filled('filter'), function ($subQuery) use ($request) {
            $filter = explode(',', $request->filter);
            $subQuery->where($filter[0], $filter[1]);
        });

        $query->when($request->filled('order'), function ($subQuery) use ($request) {
            $order = explode(',', $request->order);
            $subQuery->orderBy($order[0], $order[1]);
        });

        $summaryQuery = clone $query;

        $paginate = $query->orderBy('name', 'asc')->paginate($perPage, ['*'], 'page', $currentPage);

        return ResponseResource::collection($paginate)->additional([
            'summary' => [
                'days' => $summaryQuery->calculateDays()->value('total_duration')
            ]
        ]);
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
            $this->generateService->cloneScopeStandart(new WhereOptionData(
                'uuid',
                '=',
                $request->scope_standart_uuid,
                [
                    'additional_scope_uuid' => $request->additional_scope_uuid
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

        $scopes = ModelsScopeStandart::query()
            ->doestHaveTransactionDetail($request->input('additional_scope_uuid', null))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $scopes;
    }
}
