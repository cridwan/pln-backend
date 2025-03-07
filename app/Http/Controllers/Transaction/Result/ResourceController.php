<?php

namespace App\Http\Controllers\Transaction\Result;

use App\Exports\ConsMatExport;
use App\Exports\ManpowerExport;
use App\Exports\PartExport;
use App\Exports\ScopeStandartExport;
use App\Exports\ScopeStandartSheetExport;
use App\Exports\ToolsExport;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use Dedoc\Scramble\Attributes\Group;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: ResponseMiddleware::class)]
#[Group('Transaction Result Resource')]
class ResourceController extends Controller
{
    #[Route(method: 'get', uri: 'export/consmat')]
    public function exportConsMat()
    {
        $filename = date('YmdHis') . '-consumable-material.xlsx';
        return Excel::download(new ConsMatExport('CONSUMABLE MATERIAL'), $filename);
    }

    #[Route(method: 'get', uri: 'export/part')]
    public function exportPart()
    {
        $filename = date('YmdHis') . '-part.xlsx';
        return Excel::download(new PartExport('PART'), $filename);
    }

    #[Route(method: 'get', uri: 'export/manpower')]
    public function exportManpower()
    {
        $filename = date('YmdHis') . '-manpower.xlsx';
        return Excel::download(new ManpowerExport('MANPOWER'), $filename);
    }

    #[Route(method: 'get', uri: 'export/tools')]
    public function exportTools()
    {
        $filename = date('YmdHis') . '-tools.xlsx';
        return Excel::download(new ToolsExport('TOOLS'), $filename);
    }

    #[Route(method: 'get', uri: 'export/scope-standart')]
    public function exportScopeStandart()
    {
        $filename = date('YmdHis') . '-scope-standart.xlsx';
        return (new ScopeStandartSheetExport())->download($filename);
    }
}
