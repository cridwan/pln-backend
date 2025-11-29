<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group('Master User')]
class UserController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
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

    /**
     * list data
     */
    #[Route(method: 'get', uri: '/')]
    public function index(Request $request)
    {
        $perPage = $request->filled('perPage') ? $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? $request->currentPage : 1;

        $query = User::query()
            ->when($request->search, function ($subQuery) use ($request) {
                $subQuery->where('name', 'like', '%' . $request->search . '%');
            });

        $builder = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::callback('role', function (Builder $query, $value) {
                    $query->whereHas('roles', fn($role) => $role->where('name', '=', $value));
                }),
                AllowedFilter::callback('area', function (Builder $query, $value) {
                    $value = $value == "yes" ? true : false;
                    $query->when($value, function ($where) {
                        $where->where('area_uuid', '=', auth()->user()->area_uuid);
                    });
                })
            ])
            ->with(['roles', 'area', 'activityLog.updatedBy', 'activityLog.createdBy'])
            ->allowedSorts('name')
            ->defaultSort('name');

        return $builder->paginate($perPage, ['*'], 'page', $currentPage);
    }

    /**
     * store data
     */
    #[Route(method: 'post', uri: '/')]
    public function store(UserRequest $request)
    {
        $user = User::create([
            ...$request->except('password'),
            'password' => Hash::make($request->password),
            'first_create' => true,
        ]);

        $user->syncRoles($request->roles);

        return $user;
    }

    /**
     * show data
     */
    #[Route(method: 'get', uri: '{uuid}')]
    public function show(string $uuid)
    {
        $user = User::find($uuid);

        if (!$user) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        $user->loadMissing('roles');

        return $user;
    }

    /**
     * update data
     */
    #[Route(method: 'put', uri: '{uuid}')]
    public function update(UserRequest $request, string $uuid)
    {
        $user = User::find($uuid);

        if (!$user) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        if ($request->filled('password')) {
            $request->merge([
                'password' => Hash::make($request->password)
            ]);
        }

        $user->syncRoles($request->roles);

        $user->update($request->all());
    }

    /**
     * delete data
     */
    #[Route(method: 'delete', uri: '{uuid}')]
    public function delete(string $uuid)
    {
        $user = User::find($uuid);

        if (!$user) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        return $user->delete();
    }
}
