<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\PermissionRoleMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\InspectionType;
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
#[Group('Master Inspection Type')]
class InspectionTypeController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show', 'index']),
            new Middleware(
                PermissionRoleMiddleware::using(
                    [
                        PermissionEnum::INSPECTION_TYPE,
                        RoleEnum::PLANNER
                    ]
                ),
                except: ['list', 'show', 'index']
            )
        ];
    }
    use HasList, HasApiResource, ImportExportExcel;

    protected $model = InspectionType::class;
    protected array $search = ['name'];
    protected array $with = ['machine', 'machine.unit', 'machine.unit.location', 'sequence.document'];
    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => ['required'],
            'machine_uuid' => ['required', Rule::exists('masterdata.machines', 'uuid')],
            'sequence_uuid' => ['nullable', Rule::exists('sequences', 'uuid')]
        ];
    }
}
