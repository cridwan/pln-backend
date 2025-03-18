<?php


namespace App\Traits;

use App\Http\Requests\ListRequest;
use Illuminate\Http\Request;
use Spatie\RouteDiscovery\Attributes\Route;

trait HasList
{
    #[Route(method: 'get', name: "list")]
    public function list(ListRequest $request)
    {
        $with = isset($this->with) ? $this->with : [];
        $searchColumn = isset($this->search) ? $this->search : [];
        $query = $this->model::query();
        $query->with($with);

        $query->when($request->filled('search'), function ($subQuery) use ($request, $searchColumn) {
            $subQuery->where(function ($search) use ($request, $searchColumn) {
                foreach ($searchColumn as $index => $item) {
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

        return $query->get();
    }
}
