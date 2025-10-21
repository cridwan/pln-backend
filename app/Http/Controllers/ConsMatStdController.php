<?php

namespace App\Http\Controllers;

use App\Data\PaginationData;
use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\PermissionRoleMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Models\ConsMatStd;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Consumable Material STD')]
class ConsMatStdController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show', 'index', 'grouping']),
            new Middleware(
                RoleMiddleware::using(
                    RoleEnum::masterRole(),
                ),
                except: ['list', 'show', 'index', 'grouping']
            )
        ];
    }

    use HasList, HasApiResource, ImportExportExcel;

    protected $model = ConsMatStd::class;
    protected array $search = [];
    protected array $order = ['consmat.name', 'asc'];
    protected array $with = ['consmat.globalUnit', 'activity', 'activity.equipment', 'activity.equipment.scopeStandart', 'activity.equipment.scopeStandart.inspectionType', 'activity.equipment.scopeStandart.inspectionType.machine', 'activity.equipment.scopeStandart.inspectionType.machine.unit', 'activity.equipment.scopeStandart.inspectionType.machine.unit.location', 'activity.equipment.scopeStandart.subBidang', 'activity.equipment.scopeStandart.subBidang.bidang'];
    protected $rules = [
        'activity_uuid' => 'required|exists:activities,uuid',
        'cons_mat_uuid' => 'required|exists:const_mats,uuid',
        'qty' => 'required',
    ];


    /**
     * list data by grouping data
     */
    #[Route(method: 'get', uri: 'grouping')]
    public function grouping(Request $request)
    {
        $pagination = new PaginationData($request);
        $query = ConsMatStd::query()
            ->select([
                'cons_mat_uuid',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('GROUP_CONCAT(uuid separator ";") as uuid')
            ])
            ->with($this->with)
            ->groupBy('cons_mat_uuid');

        return $query->paginate($pagination->limit, ['*'], 'page', $pagination->page);
    }
}
