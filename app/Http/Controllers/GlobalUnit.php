<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Http\Middleware\PermissionMiddleware;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

class GlobalUnit extends Controller implements HasMiddleware
{
    use HasList, HasApiResource;

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
            new Middleware(
                PermissionMiddleware::using(
                    [
                        PermissionEnum::GLOBAL_UNIT
                    ]
                ),
                except: ['list']
            )
        ];
    }

    protected $model =  GlobalUnit::class;
    protected array $search = ['name'];
    protected array $with = [];
    protected $rules = [];
}
