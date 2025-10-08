<?php

namespace App\Http\Controllers\Transaction\Part;

use App\Data\PaginationData;
use App\Enums\AuthPermissionEnum;
use App\Enums\ConnectionEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Requests\Transaction\ClonePartRequest;
use App\Models\PartStd;
use App\Models\Transaction\Activity;
use App\Models\Transaction\Part;
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

#[Group(name: 'Transaction Part Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination, HasApiResource;

    protected $model = Part::class;
    protected array $search = ['no_drawing', 'name'];
    protected array $with = ['part', 'part.globalUnit'];

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
            'qty' => 'required',
            'noDrawing' => 'nullable',
            'note' => 'nullable',
            'global_unit_uuid' => ['required', Rule::exists('masterdata.global_units', 'uuid')],
            'project_uuid' => ['nullable', Rule::exists('transaction.projects', 'uuid')],
            'additional_scope_uuid' => 'nullable'
        ];
    }

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(ClonePartRequest $request)
    {
        \Log::info('trigger');
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate part std
            PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                ->where('uuid', $request->part_uuid)
                ->each(function ($row) use ($request) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('part_stds');
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
        $query = Part::query()
            ->select([
                'part_uuid',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('GROUP_CONCAT(uuid separator ";") as uuid')
            ])
            ->with($this->with)
            ->groupBy('part_uuid');

        return $query->paginate($pagination->limit, ['*'], 'page', $pagination->page);
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
            ->whereNotExists(function ($subQuery) use ($request) {
                $trxDb = \DB::connection(ConnectionEnum::TRANSACTION->value)->getDatabaseName();
                $subQuery->selectRaw(1)
                    ->from($trxDb . '.part_stds as trx')
                    ->join($trxDb . '.activities as ac', 'ac.uuid', '=', 'trx.activity_uuid')
                    ->join($trxDb . '.equipment as eq', 'eq.uuid', '=', 'ac.equipment_uuid')
                    ->join($trxDb . '.scope_standarts as ss', 'ss.uuid', '=', 'eq.scope_standart_uuid')
                    ->whereColumn('trx.original_uuid', '=', 'part_stds.uuid')
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
