<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Enums\PermissionEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Requests\AdditionalScopeRequest;
use App\Http\Requests\ScopeStandartMasterRequest;
use App\Models\AdditionalScope;
use App\Models\ScopeStandart;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group("Master Additional Scope")]
class AdditionalScopeController extends Controller
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
            new Middleware(
                PermissionMiddleware::using(
                    [
                        PermissionEnum::ADDITIONAL_SCOPE
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


        $query = AdditionalScope::query();
        $query->with(['details', 'inspectionType.machine.unit.location']);
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

        return $query->has('inspectionType')->orderBy('created_at', 'DESC')->paginate($perPage, ['*'], 'page', $currentPage);
    }

    #[Route(method: 'post', uri: '/')]
    public function store(AdditionalScopeRequest $request)
    {
        $additionalScope =  AdditionalScope::create($request->except('details'));

        $additionalScope->details()->createMany($request->details);

        return $additionalScope;
    }

    #[Route(method: 'get', uri: '{uuid}')]
    public function show(string $uuid)
    {
        $additionalScope = AdditionalScope::find($uuid);

        if (!$additionalScope) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        return $additionalScope;
    }

    #[Route(method: 'put', uri: '{uuid}')]
    public function update(AdditionalScopeRequest $request, string $uuid)
    {
        $additionalScope = AdditionalScope::find($uuid);

        if (!$additionalScope) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        $additionalScope->update($request->except('details'));

        foreach ($request->details as $detail) {
            $additionalScope->details()->updateOrCreate([
                'name' => $detail['name'],
                'additional_scope_uuid' => $detail['additional_scope_uuid']
            ], [
                'name' => $detail['name'],
                'additional_scope_uuid' => $detail['additional_scope_uuid']
            ]);
        }

        return $additionalScope;
    }

    #[Route(method: 'delete', uri: '{uuid}')]
    public function delete(string $uuid)
    {
        $additionalScope = AdditionalScope::find($uuid);

        if (!$additionalScope) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        return $additionalScope->delete();
    }
}
