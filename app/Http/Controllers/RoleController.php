<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Master Role')]
#[Route(middleware: ResponseMiddleware::class)]
class RoleController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
            new Middleware(
                PermissionMiddleware::using(
                    [
                        PermissionEnum::ROLE
                    ]
                ),
            )
        ];
    }

    #[Route(method: 'get', uri: 'list/permissions')]
    public function permissions()
    {
        return Permission::latest()->get();
    }

    #[Route(method: 'get', uri: '/')]
    public function index(Request $request)
    {
        $perPage = $request->filled('perPage') ? $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? $request->currentPage : 1;


        $query = Role::query();
        $query->when($request->filled('search'), function ($subQuery) use ($request) {
            $subQuery->where(function ($search) use ($request) {
                $search->where('name', 'like', "%$request->search%");
            });
        });

        $query->when($request->filled('filter'), function ($subQuery) use ($request) {
            $filter = explode(',', $request->filter);
            $subQuery->where($filter[0], $filter[1]);
        });

        $query->when($request->filled('filters'), function ($subQuery) use ($request) {
            $filters = explode('&', $request->filters);
            foreach ($filters as $filter) {
                $filter = explode(',', $filter);
                $subQuery->where($filter[0], $filter[1]);
            }
        });

        $query->when($request->filled('order'), function ($subQuery) use ($request) {
            $order = explode(',', $request->order);
            $subQuery->orderBy($order[0], $order[1]);
        });

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }

    #[Route(method: 'post', uri: '/')]
    public function store(RoleRequest $request)
    {
        $role =  Role::create($request->except('permissions'));

        $role->syncPermissions($request->permissions);
    }

    #[Route(method: 'get', uri: '{uuid}')]
    public function show(string $uuid)
    {
        $role = Role::find($uuid);

        if (!$role) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        return $role;
    }

    #[Route(method: 'put', uri: '{uuid}')]
    public function update(RoleRequest $request, string $uuid)
    {
        $role = Role::find($uuid);

        if (!$role) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        $role->update($request->except('permissions'));

        $role->syncPermissions($request->permissions);

        return $role;
    }

    #[Route(method: 'delete', uri: '{uuid}')]
    public function delete(string $uuid)
    {
        $role = Role::find($uuid);

        if (!$role) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        return $role->delete();
    }
}
