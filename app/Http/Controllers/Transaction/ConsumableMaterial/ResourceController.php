<?php

namespace App\Http\Controllers\Transaction\ConsumableMaterial;

use App\Data\PaginationData;
use App\Enums\AuthPermissionEnum;
use App\Enums\ConnectionEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Requests\Transaction\CloneConsMatRequest;
use App\Http\Resources\PaginationResource;
use App\Models\ConsMatStd;
use App\Models\Transaction\Activity;
use App\Models\Transaction\ConsMat;
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

#[Group('Transaction Consumable Material Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination, HasApiResource;

    protected $model = ConsMat::class;
    protected array $search = ['name', 'merk'];
    protected array $with = ['consmat.globalUnit'];
    protected $rules = [];

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show', 'index', 'pagination', 'grouping']),
            new Middleware(
                RoleMiddleware::using(
                    RoleEnum::transactionRole()
                ),
                except: ['list', 'show', 'index', 'pagination', 'grouping']
            )
        ];
    }

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => 'required',
            'merk' => 'nullable',
            'qty' => 'required',
            'global_unit_uuid' => ['required', Rule::exists('masterdata.global_units', 'uuid')],
            'project_uuid' => 'nullable',
            'additional_scope_uuid' => 'nullable'
        ];
    }

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(CloneConsMatRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate consumable material
            ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid', 'qty')
                ->where('uuid', $request->cons_mat_uuid)
                ->each(function ($row) use ($request) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('cons_mat_stds');
                    $duplicate->activity_uuid = $request->activity_uuid;
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
        $query = ConsMat::query()
            ->select([
                'cons_mat_uuid',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('GROUP_CONCAT(uuid separator ";") as uuid')
            ])
            ->with($this->with)
            ->groupBy('cons_mat_uuid');

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
            ->whereNotExists(function ($subQuery) use ($request) {
                $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.cons_mat_stds as trx')
                    ->join($trxDb . '.activities as ac', 'ac.uuid', '=', 'trx.activity_uuid')
                    ->join($trxDb . '.equipment as eq', 'eq.uuid', '=', 'ac.equipment_uuid')
                    ->join($trxDb . '.scope_standarts as ss', 'ss.uuid', '=', 'eq.scope_standart_uuid')
                    ->whereColumn('trx.original_uuid', '=', 'cons_mat_stds.uuid')
                    ->when($request->filled('project_uuid'), fn($query) => $query->where('ss.project_uuid', '=', $request->get('project_uuid')))
                    ->when($request->filled('additional_scope_uuid'), fn($query) => $query->where('ss.additional_scope_uuid', '=', $request->get('additional_scope_uuid')));
            })
            ->when($trxActivity, fn($query) => $query->where('activity_uuid', '=', $trxActivity->original_uuid))
            ->when($request->filled('project_uuid'), fn($query) => $query->whereHas('activity.equipment.scopeStandart', fn($scope) => $scope->doesntHave('additionalScope')))
            ->when($request->filled('additional_scope'), fn($query) => $query->whereHas('activity.equipment.scopeStandart', fn($scope) => $scope->doesntHave('inspectionType')))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $equipment;
    }
}
