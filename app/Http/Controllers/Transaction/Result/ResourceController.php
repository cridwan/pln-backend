<?php

namespace App\Http\Controllers\Transaction\Result;

use App\Enums\AuthPermissionEnum;
use App\Exceptions\BadRequestException;
use App\Exports\ConsMatExport;
use App\Exports\ManpowerExport;
use App\Exports\PartExport;
use App\Exports\ScopeStandartSheetExport;
use App\Exports\ToolsExport;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\Transaction\Project;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: ResponseMiddleware::class)]
#[Group('Transaction Result Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            // new Middleware(AuthPermissionEnum::AUTH_API->value),
        ];
    }

    #[Route(method: 'get', uri: 'export/consmat')]
    public function exportConsMat(Request $request)
    {
        if ($request->isNotFilled('project_uuid')) {
            throw new BadRequestException('Project tidak terpilih');
        }

        $project = Project::find($request->project_uuid);

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->loadMissing(['inspectionType.machine']);
        $filename = date('YmdHis') . '-consumable-material.xlsx';
        return Excel::download(new ConsMatExport('CONSUMABLE MATERIAL', $project), $filename);
    }

    #[Route(method: 'get', uri: 'export/part')]
    public function exportPart(Request $request)
    {
        if ($request->isNotFilled('project_uuid')) {
            throw new BadRequestException('Project tidak terpilih');
        }

        $project = Project::find($request->project_uuid);

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->loadMissing(['inspectionType.machine']);
        $filename = date('YmdHis') . '-part.xlsx';
        return Excel::download(new PartExport('PART', $project), $filename);
    }

    #[Route(method: 'get', uri: 'export/manpower')]
    public function exportManpower(Request $request)
    {
        if ($request->isNotFilled('project_uuid')) {
            throw new BadRequestException('Project tidak terpilih');
        }

        $project = Project::find($request->project_uuid);

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->loadMissing(['inspectionType.machine']);
        $filename = date('YmdHis') . '-manpower.xlsx';
        return Excel::download(new ManpowerExport('MANPOWER', $project), $filename);
    }

    #[Route(method: 'get', uri: 'export/tools')]
    public function exportTools(Request $request)
    {
        if ($request->isNotFilled('project_uuid')) {
            throw new BadRequestException('Project tidak terpilih');
        }

        $project = Project::find($request->project_uuid);

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->loadMissing(['inspectionType.machine']);
        $filename = date('YmdHis') . '-tools.xlsx';
        return Excel::download(new ToolsExport('TOOLS', $project), $filename);
    }

    #[Route(method: 'get', uri: 'export/scope-standart')]
    public function exportScopeStandart(Request $request)
    {
        if ($request->isNotFilled('project_uuid')) {
            throw new BadRequestException('Project tidak terpilih');
        }

        $project = Project::find($request->project_uuid);

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->loadMissing(['inspectionType.machine']);

        $filename = date('YmdHis') . '-scope-standart.xlsx';
        return (new ScopeStandartSheetExport($project))->download($filename);
    }
}
