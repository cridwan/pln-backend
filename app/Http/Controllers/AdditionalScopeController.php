<?php

namespace App\Http\Controllers;

use App\Core\Master\AdditionalScopeCore;
use App\Exceptions\BadRequestException;
use App\Http\Requests\AdditionalScopeRequest;
use App\Models\AdditionalScope;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group("Master Additional Scope")]
class AdditionalScopeController extends AdditionalScopeCore
{
    use InitCore;
    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
    /**
     * list data
     */
    #[Route(method: 'get', uri: '/')]
    public function index(Request $request)
    {
        $perPage = $request->filled('perPage') ? $request->perPage : 10;
        $currentPage = $request->filled('currentPage') ? $request->currentPage : 1;


        $query = AdditionalScope::query()
            ->hasTransaction();
        $query->with(['inspectionType.machine.unit.location', 'sequence.document', 'activityLog.createdBy', 'activityLog.updatedBy']);
        $query->when($request->filled('search'), function ($subQuery) use ($request) {
            $subQuery->where(function ($search) use ($request) {
                $search->where('name', 'like', "%$request->search%");
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

        $query->fromTransaction();

        return $query->has('inspectionType')->orderBy('name', 'asc')->paginate($perPage, ['*'], 'page', $currentPage);
    }

    /**
     * store data
     */
    #[Route(method: 'post', uri: '/')]
    public function store(AdditionalScopeRequest $request)
    {
        $additionalScope = AdditionalScope::create($request->except('details'));

        return $additionalScope;
    }

    /**
     * show data
     */
    #[Route(method: 'get', uri: '{uuid}')]
    public function show(string $uuid)
    {
        $additionalScope = AdditionalScope::find($uuid);

        if (!$additionalScope) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        return $additionalScope;
    }

    /**
     * update data
     */
    #[Route(method: 'put', uri: '{uuid}')]
    public function update(AdditionalScopeRequest $request, string $uuid)
    {
        $additionalScope = AdditionalScope::find($uuid);

        if (!$additionalScope) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        $additionalScope->update($request->except('details'));


        return $additionalScope;
    }

    /**
     * delete data
     */
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
