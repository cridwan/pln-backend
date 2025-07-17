<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\Part;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Master Part')]
#[Route(middleware: ResponseMiddleware::class)]
class PartController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
            new Middleware(
                PermissionMiddleware::using(
                    [
                        PermissionEnum::PART
                    ]
                ),
                except: ['list']
            )
        ];
    }

    use HasList, HasApiResource, ImportExportExcel;

    protected $model = Part::class;
    protected array $search = ['name'];
    protected array $with = ['globalUnit', 'inspectionType.machine.unit.location'];
    protected $rules = [
        "name" => "required",
        "qty" => "required",
        "note" => "required",
        "no_drawing" => "required",
        "global_unit_uuid" => "required",
        "additional_scope_uuid" => "nullable",
        "inspection_type_uuid" => "required",
        "size" => "nullable",
        "location" => "nullable",
    ];
}
