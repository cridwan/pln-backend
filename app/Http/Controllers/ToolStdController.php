<?php

namespace App\Http\Controllers;

use App\Core\Master\ToolStdCore;
use App\Data\PaginationData;
use App\Models\ToolsStd;
use App\Traits\InitCore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

class ToolStdController extends ToolStdCore
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
        $query = ToolsStd::query()
            ->select([
                'tools_uuid',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('GROUP_CONCAT(uuid separator ";") as uuid')
            ])
            ->with($this->with)
            ->groupBy('tools_uuid');

        return $query->paginate($pagination->limit, ['*'], 'page', $pagination->page);
    }
}
