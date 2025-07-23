<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\PermissionRoleMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\Unit;
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
#[Group('Master Unit')]
class UnitController extends Controller implements HasMiddleware
{
    use HasList, HasApiResource, ImportExportExcel;

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list']),
            new Middleware(
                PermissionRoleMiddleware::using(
                    [
                        PermissionEnum::UNIT,
                        RoleEnum::PLANNER
                    ]
                ),
                except: ['list']
            )
        ];
    }

    protected $model = Unit::class;
    protected $search = ['name'];
    protected $with = ['location'];
    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => ['required'],
            'location_uuid' => ['required', Rule::exists('masterdata.locations', 'uuid')]
        ];
    }
}
