<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\RoleMiddleware;
use App\Models\QcPlan;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

class QcPlanController extends Controller
{
    use HasList, HasApiResource, ImportExportExcel;

    #[DoNotDiscover()]
    public static function middleware()
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

    protected $model = QcPlan::class;
    protected array $search = ['name'];
    protected array $order = ['name', 'asc'];
    protected array $with = [];
    protected $rules = [
        "name" => "required",
    ];
}
