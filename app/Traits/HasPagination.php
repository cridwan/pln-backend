<?php


namespace App\Traits;

use Illuminate\Http\Request;

trait HasPagination
{
    public $model;

    public array $search  = [];

    public function pagination(Request $request)
    {
        $perPage = $request->filled('perPage') ? (int) $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? (int) $request->currentPage : 1;
        $query = $this->model::query();

        $query->when($request->filled('search'), function ($subQuery) use ($request) {
            $subQuery->where(function ($search) use ($request) {
                foreach ($this->search as $item) {
                    $explode = explode('.', $item);
                    if (count($explode) > 1) {
                        $search->whereRelation($explode[0], $explode[1], 'like', "%$request->search%");
                    } else {
                        $search->where($item, 'like', "%$request->search%");
                    }
                }
            });
        });

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }
}
