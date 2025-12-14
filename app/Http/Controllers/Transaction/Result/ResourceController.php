<?php

namespace App\Http\Controllers\Transaction\Result;

use App\Enums\AuthPermissionEnum;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Exports\BudgetActivityExport;
use App\Exports\ConsMatExport;
use App\Exports\HseExport;
use App\Exports\ManpowerExport;
use App\Exports\PartExport;
use App\Exports\QcPlanExport;
use App\Exports\ScopeStandartExport;
use App\Exports\ToolsExport;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Middleware\RoleMiddleware;
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
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show', 'index', 'pagination']),
            new Middleware(
                RoleMiddleware::using(
                    RoleEnum::transactionRole()
                ),
                except: ['list', 'show', 'index', 'pagination']
            )
        ];
    }

    /**
     * download recap qc plan
     */
    #[Route(method: 'get', uri: 'export/qc-plan')]
    public function exportQcPlan(Request $request)
    {
        if ($request->isNotFilled('project_uuid')) {
            throw new BadRequestException('Project tidak terpilih');
        }

        $project = Project::find($request->project_uuid);

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->loadMissing(['inspectionType.machine']);
        return (new QcPlanExport($project))->execute();
    }

    /**
     * download recap hse
     */
    #[Route(method: 'get', uri: 'export/hse')]
    public function exportHse(Request $request)
    {
        if ($request->isNotFilled('project_uuid')) {
            throw new BadRequestException('Project tidak terpilih');
        }

        $project = Project::find($request->project_uuid);

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->loadMissing(['inspectionType.machine']);
        return (new HseExport($project))->execute();
    }

    /**
     * download recap consmat
     */
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
        return (new ConsMatExport($project))->execute();
    }

    /**
     * download recap part
     */
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
        return (new PartExport($project))->execute();
    }

    /**
     * download recap manpower
     */
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
        return (new ManpowerExport($project))->execute();
    }

    /**
     * download recap tools
     */
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
        return Excel::download(new ToolsExport($project), $filename);
    }

    /**
     * download recap scope standart
     */
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

        $type = $request->get('type', 'SCOPE STANDART');

        return (new ScopeStandartExport($project, type: $type))->execute();
    }

    /**
     * download recap budget activity
     */
    #[Route(method: 'get', uri: 'export/budget-activity')]
    public function exportBudgetActivity(Request $request)
    {
        if ($request->isNotFilled('project_uuid')) {
            throw new BadRequestException('Project tidak terpilih');
        }

        $project = Project::find($request->project_uuid);

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->loadMissing(['inspectionType.machine.unit.location']);
        $type = $request->get('type', 'SCOPE STANDART');

        return (new BudgetActivityExport($project, type: $type))->execute();
    }
}
