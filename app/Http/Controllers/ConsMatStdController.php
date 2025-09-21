<?php

namespace App\Http\Controllers;

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
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
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
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show', 'index']),
            new Middleware(
                RoleMiddleware::using(
                    [
                        RoleEnum::SUPERUSER
                    ]
                ),
                except: ['list', 'show', 'index']
            )
        ];
    }

    use HasList, HasApiResource, ImportExportExcel;

    protected $model = ConsMatStd::class;
    protected array $search = [];
    protected array $with = ['consmat', 'activity', 'activity.equipment', 'activity.equipment.scopeStandart', 'activity.equipment.scopeStandart.inspectionType', 'activity.equipment.scopeStandart.inspectionType.machine', 'activity.equipment.scopeStandart.inspectionType.machine.unit', 'activity.equipment.scopeStandart.inspectionType.machine.unit.location', 'activity.equipment.scopeStandart.subBidang', 'activity.equipment.scopeStandart.subBidang.bidang'];
    protected $rules = [
        'activity_uuid' => 'required|exists:activities,uuid',
        'cons_mat_uuid' => 'required|exists:const_mats,uuid',
        'qty' => 'required',
    ];
}
