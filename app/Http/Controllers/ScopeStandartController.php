<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Requests\ScopeStandartMasterRequest;
use App\Models\ScopeStandart;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

class ScopeStandartController extends Controller
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
            new Middleware(
                PermissionMiddleware::using(
                    [
                        PermissionEnum::SCOPE_STANDART
                    ]
                ),
            )
        ];
    }

    #[Route(method: 'get', uri: '/')]
    public function index(Request $request)
    {
        $perPage = $request->filled('perPage') ? $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? $request->currentPage : 1;


        $query = ScopeStandart::query();
        $query->with('details');
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
    public function store(ScopeStandartMasterRequest $request)
    {
        $scopeStandart =  ScopeStandart::create($request->except('details'));

        $scopeStandart->details()->createMany($request->details);

        return $scopeStandart;
    }

    #[Route(method: 'get', uri: '{uuid}')]
    public function show(string $uuid)
    {
        $scopeStandart = ScopeStandart::find($uuid);

        if (!$scopeStandart) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        return $scopeStandart;
    }

    #[Route(method: 'put', uri: '{uuid}')]
    public function update(ScopeStandartMasterRequest $request, string $uuid)
    {
        $scopeStandart = ScopeStandart::find($uuid);

        if (!$scopeStandart) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        $scopeStandart->update($request->except('details'));

        $scopeStandart->details()->createMany($request->details);

        return $scopeStandart;
    }

    #[Route(method: 'delete', uri: '{uuid}')]
    public function delete(string $uuid)
    {
        $scopeStandart = ScopeStandart::find($uuid);

        if (!$scopeStandart) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        return $scopeStandart->delete();
    }
}
