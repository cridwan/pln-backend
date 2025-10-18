<?php

namespace App\Http\Controllers;

use App\Data\AttributeData;
use App\Enums\AuthPermissionEnum;
use App\Enums\GeneratorTypeEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Location;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use App\Traits\ImportExportExcel;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Master Location')]
class LocationController extends Controller implements HasMiddleware
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

    protected $model = Location::class;
    protected array $order = ['name', 'asc'];
    protected array $search = ['name', 'slug'];
    protected array $customAttribute = [];
    protected array $with = ['updatedBy'];
    protected $rules = [
        'name' => 'required',
        'slug' => 'required',
        'description' => 'nullable',
        'lat' => 'required',
        'lon' => 'required',
        'color' => 'required',
    ];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->customAttribute = [
            new AttributeData('uuid', 'UUID'),
            new AttributeData('name', 'NAME'),
            new AttributeData('slug', 'KODE'),
            new AttributeData('description', 'DESCRIPTION'),
            new AttributeData('lat', 'LAT'),
            new AttributeData('lon', 'LON'),
            new AttributeData('color', 'COLOR'),
            new AttributeData(function ($row) {
                $getType = GeneratorTypeEnum::getType($row->color)->name ?? '';
                return str($getType)->explode('_')->join('/');
            }, 'GENERATOR TYPE'),
            new AttributeData(function ($row) {
                return $row->updatedBy?->name ?? '';
            }, 'LAST UPDATED BY'),
            new AttributeData('created_at', 'CREATED AT'),
            new AttributeData('updated_at', 'UPDATED AT'),
        ];
    }
}
