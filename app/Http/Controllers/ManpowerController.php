<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\Manpower;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Master Manpower')]
#[Route(middleware: ResponseMiddleware::class)]
class ManpowerController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
            new Middleware(
                PermissionMiddleware::using(
                    [
                        PermissionEnum::MANPOWER
                    ]
                ),
                except: ['list']
            )
        ];
    }

    use HasList, HasApiResource;

    protected $model = Manpower::class;
    protected array $search = ['name'];
    protected array $with = ['globalUnit', 'inspectionType.machine'];
    protected $rules = [];
}
