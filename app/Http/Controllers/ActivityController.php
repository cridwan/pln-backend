<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\PermissionRoleMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\Activity;
use App\Models\Bidang;
use App\Models\Equipment;
use App\Models\SubBidang;
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
class ActivityController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show']),
            new Middleware(
                PermissionRoleMiddleware::using(
                    [
                        PermissionEnum::LOCATION,
                        RoleEnum::PLANNER
                    ]
                ),
                except: ['list', 'show']
            )
        ];
    }

    use HasList, HasApiResource, ImportExportExcel;

    protected $model = Activity::class;
    protected array $search = ['name'];
    protected array $with = [];
    protected $rules = [
        'name' => 'required',
        'duration' => 'required',
        'equipment_uuid' => 'required|exists:equipments,uuid',
        'link_ik1' => 'nullable',
        'link_ik2' => 'nullable',
    ];
}
