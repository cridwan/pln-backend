<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\PermissionRoleMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\Machine;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group('Master Machine')]
class MachineController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list']),
            new Middleware(
                PermissionRoleMiddleware::using(
                    [
                        PermissionEnum::MACHINE,
                        RoleEnum::PLANNER
                    ]
                ),
                except: ['list']
            )
        ];
    }

    use HasList, HasApiResource, ImportExportExcel;

    protected $model = Machine::class;
    protected $search = ['name', 'unit.name'];
    protected $with = ['unit'];
    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => ['required'],
            'unit_uuid' => ['required', Rule::exists('masterdata.units', 'uuid')]
        ];
    }
}
