<?php


namespace App\Traits;

use App\Http\Requests\PaginationRequest;
use App\Http\Resources\PaginationResource;

trait HasPagination
{
    /**
     * get pagination data
     * @param \App\Http\Requests\PaginationRequest $request
     */
    public function pagination(PaginationRequest $request)
    {
        $perPage = $request->filled('perPage') ? (int) $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? (int) $request->currentPage : 1;
        $with = isset($this->with) ? $this->with : [];
        $order = isset($this->order) ? $this->order : ['created_at', 'desc'];
        $searchColumn = isset($this->search) ? $this->search : [];
        $query = method_exists($this, 'query') ? $this->query() : $this->model::query();
        $query->with($with);

        $query->when($request->filled('search'), function ($subQuery) use ($request, $searchColumn) {
            $subQuery->where(function ($search) use ($request, $searchColumn) {
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

        $pagination = $query->orderBy($column, $direction)->paginate($perPage, ['*'], 'page', $currentPage);

        return PaginationResource::collection($pagination);
    }
}
