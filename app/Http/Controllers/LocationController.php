<?php

namespace App\Http\Controllers;

use App\Core\Master\LocationCore;
use App\Enums\AuthPermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Requests\PaginationRequest;
use App\Models\Location;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Master Location')]
class LocationController extends LocationCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }

    #[DoNotDiscover]
    public static function middleware(): array
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show']),
            new Middleware(
                RoleMiddleware::using(
                    RoleEnum::masterRole(),
                ),
                except: ['list', 'show', 'index']
            )
        ];
    }

    /**
     * get list data pagination
     */
    #[Route(method: 'get', uri: '/')]
    public function index(PaginationRequest $request)
    {
        $perPage = $request->filled('perPage') ? (int) $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? (int) $request->currentPage : 1;
        $with = isset($this->with) ? $this->with : [];
        $order = isset($this->order) ? $this->order : ['created_at', 'desc'];
        $query = Location::query()
            ->with($with);

        $query->when(auth()->user() && !auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($where) {
            $where->whereHas('subArea', fn($query) => $query->where('area_uuid', '=', auth()->user()->area_uuid));
        });

        $query->when($request->filled('search'), function ($subQuery) use ($request) {
            $subQuery->where(function ($search) use ($request) {
                foreach ($this->search as $index => $item) {
                    if ($index == 0) {
                        $explode = explode('.', $item);
                        if (count($explode) > 1) {
                            $search->whereHas($explode[0], fn($related) => $related->where($explode[1], 'like', "%$request->search%"));
                        } else {
                            $search->where($item, 'like', "%$request->search%");
                        }
                    } else {
                        $explode = explode('.', $item);
                        if (count($explode) > 1) {
                            $search->orWhereHas($explode[0], fn($related) => $related->where($explode[1], 'like', "%$request->search%"));
                        } else {
                            $search->orWhere($item, 'like', "%$request->search%");
                        }
                    }
                }
            });
        });

        $query->when($request->filled('filter'), function ($subQuery) use ($request) {
            $filter = explode(',', $request->filter);
            $subQuery->where($filter[0], $filter[1]);
        });

        $query->when($request->filled('order'), function ($subQuery) use ($request) {
            $order = explode(',', $request->order);
            $subQuery->orderBy($order[0], $order[1]);
        });

        if (method_exists($this->model, 'scopeFromTransaction')) {
            $query->fromTransaction();
        }

        [$column, $direction] = $order;

        if (str($column)->contains('.')) {
            [$relation, $attribute] = str($column)->explode('.');
            $query->withAggregate($relation, $attribute);
            $column = "{$relation}_{$attribute}";
        }
        return $query->orderBy($column, $direction)->paginate($perPage, ['*'], 'page', $currentPage);
    }
}
