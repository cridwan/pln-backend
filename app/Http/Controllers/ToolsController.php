<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\Tools;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Master Tools')]
class ToolsController extends Controller implements HasMiddleware
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
                        PermissionEnum::TOOLS
                    ]
                ),
                except: ['list']
            )
        ];
    }

    protected $model = Tools::class;
    protected array $search = ['name'];
    protected array $with = ['globalUnit', 'inspectionType.machine.unit.location'];
    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => 'required',
            'qty' => 'required',
            'global_unit_uuid' => ['required', Rule::exists('masterdata.global_units', 'uuid')],
            'inspection_type_uuid' => ['required', Rule::exists('masterdata.inspection_types', 'uuid')],
            'section' => 'required',
            'additional_scope_uuid' => 'nullable'
        ];
    }
}
