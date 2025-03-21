<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
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
            new Middleware(AuthPermissionEnum::AUTH_API->value),
            new Middleware(
                PermissionMiddleware::using(
                    [
                        PermissionEnum::USER
                    ]
                )
            )
        ];
    }

    #[Route(method: 'get', uri: '/')]
    public function index(Request $request)
    {
        $perPage = $request->filled('perPage') ? $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? $request->currentPage : 1;


        $query = User::query();
        $query->with('roles');
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

        return $query->orderBy('created_at', 'DESC')->paginate($perPage, ['*'], 'page', $currentPage);
    }

    #[Route(method: 'post', uri: '/')]
    public function store(UserRequest $request)
    {
        return User::create([
            $request->except('password'),
            'password' => Hash::make($request->password)
        ]);
    }

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

        $user->update($request->all());
    }

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
