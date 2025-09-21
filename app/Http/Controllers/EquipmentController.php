<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\PermissionRoleMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Equipment;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Master Equipment')]
class EquipmentController extends Controller implements HasMiddleware
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

    protected $model = Equipment::class;
    protected array $search = [];
    protected array $with = ['scopeStandart', 'scopeStandart.inspectionType', 'scopeStandart.inspectionType.machine', 'scopeStandart.inspectionType.machine.unit', 'scopeStandart.inspectionType.machine.unit.location', 'scopeStandart.subBidang', 'scopeStandart.subBidang.bidang'];
    protected $rules = [
        'name' => 'required',
        'scope_standart_uuid' => 'required|exists:scope_standarts,uuid',
        'link_ik1' => 'nullable',
        'link_ik2' => 'nullable',
    ];
}
