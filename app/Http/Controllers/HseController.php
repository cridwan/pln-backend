<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\PermissionRoleMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Bidang;
use App\Models\Hse;
use App\Models\SubBidang;
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
#[Group(name: 'Master HSE')]
class HseController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show', 'index']),
            new Middleware(
                RoleMiddleware::using(
                    [
                        RoleEnum::SUPERUSER
                    ]
                ),
                except: ['list', 'show', 'index']
            )
        ];
    }

    use HasList, HasApiResource, ImportExportExcel;

    protected $model = Hse::class;
    protected array $search = [];
    protected array $with = ['hseDoc', 'inspectionType.machine.unit.location'];
    protected $rules = [
        'inspection_type_uuid' => 'required|exists:inspection_types,uuid',
        'hse_doc_uuid' => 'required|exists:hse_docs,uuid'
    ];
}
