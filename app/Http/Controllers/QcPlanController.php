<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Http\Middleware\PermissionMiddleware;
use App\Models\QcPlan;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

class QcPlanController extends Controller
{
    use HasList, HasApiResource, ImportExportExcel;

    #[DoNotDiscover()]
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

    protected $model = QcPlan::class;
    protected array $search = ['name'];
    protected array $with = [];
    protected $rules = [
        "name" => "required",
    ];
}
