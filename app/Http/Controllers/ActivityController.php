<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Activity;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Master Activity')]
class ActivityController extends Controller implements HasMiddleware
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

    protected $model = Activity::class;
    protected array $search = ['name'];
    protected array $order = ['name', 'asc'];
    protected array $with = ['equipment', 'equipment.scopeStandart', 'equipment.scopeStandart.inspectionType', 'equipment.scopeStandart.inspectionType.machine', 'equipment.scopeStandart.inspectionType.machine.unit', 'equipment.scopeStandart.inspectionType.machine.unit.location', 'equipment.scopeStandart.subBidang', 'equipment.scopeStandart.subBidang.bidang'];
    protected $rules = [
        'name' => 'required',
        'duration' => 'required',
        'equipment_uuid' => 'required|exists:equipment,uuid',
        'link_ik1' => 'nullable',
        'link_ik2' => 'nullable',
    ];
}
