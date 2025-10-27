<?php

namespace App\Core;

use App\Enums\AuthPermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class MasterCore extends Controller implements BaseCore
{
    #[DoNotDiscover]
    public static function middleware(): array
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show', 'index']),
            new Middleware(
                RoleMiddleware::using(
                    RoleEnum::masterRole(),
                ),
                except: ['list', 'show', 'index']
            )
        ];
    }
}
