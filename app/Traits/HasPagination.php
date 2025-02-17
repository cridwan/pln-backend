<?php


namespace App\Traits;

use App\Http\Requests\PaginationRequest;

trait HasPagination
{
    public function pagination(PaginationRequest $request)
    {
        $perPage = $request->filled('perPage') ? (int) $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? (int) $request->currentPage : 1;
        $with = isset($this->with) ? $this->with : [];
        $query = $this->model::query();
        $query->with($with);

        $query->when($request->filled('search'), function ($subQuery) use ($request) {
            $subQuery->where(function ($search) use ($request) {
                foreach ($this->search as $item) {
                    $explode = explode('.', $item);
                    if (count($explode) > 1) {
                        $search->whereHas($explode[0], fn($related) => $related->where($explode[1], 'like', "%$request->search%"));
                    } else {
                        $search->where($item, 'like', "%$request->search%");
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

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }
}
