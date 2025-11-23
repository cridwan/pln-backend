<?php

namespace App\Http\AdditionalControllers;

use App\Core\Master\Detail\ScopeStandartCore;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Http\Requests\ScopeStandartMasterRequest;
use App\Models\ScopeStandart;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group("(Additional) Master Scope Standart")]
class ScopeStandartController extends ScopeStandartCore
{
    use InitCore;

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

        $query = ScopeStandart::query()
            ->when(!auth()->user()->hasRole(RoleEnum::SUPERUSER), function ($query) {
                $query->whereHas('additionalScope.inspectionType.machine.unit.location.subArea', function ($where) {
                    $where->where('area_uuid', '=', auth()->user()->area_uuid);
                });
            });

        $query->with($this->with());
        $query->when($request->filled('search'), callback: function ($subQuery) use ($request) {
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

        return $query->orderBy('name', 'asc')->paginate($perPage, ['*'], 'page', $currentPage);
    }

    /**
     * store data
     */
    #[Route(method: 'post', uri: '/')]
    public function store(ScopeStandartMasterRequest $request)
    {
        $scopeStandart = ScopeStandart::create($request->except('details'));

        $scopeStandart->details()->createMany($request->details);

        return $scopeStandart;
    }

    /**
     * show data
     */
    #[Route(method: 'get', uri: '{uuid}')]
    public function show(string $uuid)
    {
        $scopeStandart = ScopeStandart::find($uuid);

        if (!$scopeStandart) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        return $scopeStandart;
    }

    /**
     * update data
     */
    #[Route(method: 'put', uri: '{uuid}')]
    public function update(ScopeStandartMasterRequest $request, string $uuid)
    {
        $scopeStandart = ScopeStandart::find($uuid);

        if (!$scopeStandart) {
            throw new BadRequestException('Tidak ada data yang ditemukan');
        }

        $scopeStandart->update($request->except('details'));

        foreach ($request->details as $detail) {
            $scopeStandart->details()->updateOrCreate([
                'uuid' => $detail['uuid']
            ], [
                'name' => $detail['name'],
                'uuid' => $detail['uuid']
            ]);
        }



        return $scopeStandart;
    }

    /**
     * delete data
     */
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
