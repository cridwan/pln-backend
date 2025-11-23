<?php

namespace App\Http\Controllers;

use App\Core\Master\LocationCore;
use App\Enums\AuthPermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Requests\PaginationRequest;
use App\Models\Location;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Master Location')]
class LocationController extends LocationCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }

    #[DoNotDiscover]
    public static function middleware(): array
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show']),
            new Middleware(
                RoleMiddleware::using(
                    RoleEnum::masterRole(),
                ),
                except: ['list', 'show', 'index']
            )
        ];
    }
}
