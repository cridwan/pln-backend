<?php

namespace App\Traits;

use App\Exceptions\BadRequestException;
use App\Exceptions\ValidationErrorException;
use App\Http\Requests\PaginationRequest;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteDiscovery\Attributes\Route;

trait HasApiResource
{
    #[Route(method: 'get', uri: '/')]
    public function index(PaginationRequest $request)
    {
        $perPage = $request->filled('perPage') ? (int) $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? (int) $request->currentPage : 1;
        $with = isset($this->with) ? $this->with : [];
        $query = $this->model::query();
        $query->with($with);

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

    #[Route(method: 'get', uri: '{uuid}')]
    public function show(string $uuid)
    {
        $with = isset($this->with) ? $this->with : [];
        $findOne = $this->model::where('uuid', $uuid)->first();

        if (!$findOne) {
            throw new BadRequestException('Tidak ada data ditemukan');
        }

        $findOne->loadMissing($with);

        return $findOne;
    }

    // #[Route(method: 'post')]
    public function store()
    {
        $validator = Validator::make(request()->all(), $this->rules);

        if ($validator->fails()) {
            throw new ValidationErrorException(json_encode($validator->getMessageBag()));
        }

        return $this->model::create($validator->validated());
    }

    #[Route(method: 'put', uri: '{uuid}')]
    public function update(string $uuid)
    {
        $validator = Validator::make(request()->all(), $this->rules);

        if ($validator->fails()) {
            throw new ValidationErrorException(json_encode($validator->getMessageBag()));
        }

        $exist = $this->model::where('uuid', $uuid)->first();

        if (!$exist) {
            throw new BadRequestException("Data tidak ditemukan");
        }

        return $exist->update($validator->validated());
    }

    #[Route(method: 'delete', uri: '{uuid}')]
    public function delete(string $uuid): bool
    {
        $findOne = $this->model::where('uuid', $uuid)->first();

        if (!$findOne) {
            throw new BadRequestException('Tidak ada data ditemukan');
        }

        $findOne->delete();

        return true;
    }
}
