<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\GlobalUnit;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: ResponseMiddleware::class)]
#[Group('Master Global Unit')]
class GlobalUnitController extends Controller implements HasMiddleware
{
    use HasList, HasApiResource, ImportExportExcel;

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
    protected $rules = [
        "name" => "required"
    ];
}
