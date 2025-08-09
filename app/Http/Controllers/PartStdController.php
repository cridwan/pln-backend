<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\PermissionRoleMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\Bidang;
use App\Models\ConsMatStd;
use App\Models\ManpowerStd;
use App\Models\PartStd;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Manpower STD')]
class PartStdController extends Controller implements HasMiddleware
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

    protected $model = PartStd::class;
    protected array $search = [];
    protected array $with = ['part', 'activity.equipment.scopeStandart.inspectionType.machine.unit.location'];
    protected $rules = [
        'activity_uuid' => 'required|exists:activities,uuid',
        'part_uuid' => 'required|exists:parts,uuid',
        'qty' => 'required',
    ];
}
