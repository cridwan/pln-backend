<?php

namespace App\Http\AdditionalControllers;

use App\Core\Master\Detail\ConsumableMaterialStdCore;
use App\Data\PaginationData;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\ConsMatStd;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: '(Additional) Consumable Material STD')]
class ConsMatStdController extends ConsumableMaterialStdCore
{
    use InitCore;
    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }

    /**
     * list data by grouping data
     */
    #[Route(method: 'get', uri: 'grouping')]
    public function grouping(Request $request)
    {
        $pagination = new PaginationData($request);
        $query = ConsMatStd::query()
            ->select([
                'cons_mat_uuid',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('GROUP_CONCAT(uuid separator ";") as uuid')
            ])
            ->with($this->with)
            ->groupBy('cons_mat_uuid');

        return $query->paginate($pagination->limit, ['*'], 'page', $pagination->page);
    }
}
