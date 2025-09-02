<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\AuthPermissionEnum;
use App\Enums\ProjectStatusEnum;
use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Resources\ProjectResource;
use App\Models\Transaction\Project;
use App\Traits\HasList;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
class ProjectController extends Controller implements HasMiddleware
{
    use HasList;

    protected $model = Project::class;
    protected array $search = [];
    protected array $with = ['inspectionType'];


    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list']),
        ];
    }

    /**
     * delete project
     */
    #[Route(method: 'delete', uri: 'project/destroy/{uuid}')]
    public function destroy(string $uuid)
    {
        $project = Project::where('uuid', '=', $uuid)->first();

        \Log::info('project', [
            'data' => $project,
            'uuid' => $uuid
        ]);

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->delete();

        return [
            'message' => 'Data berhasil di delete'
        ];
    }

    /**
     * get one project
     */
    #[Route(method: 'get', uri: 'project/{uuid}/show')]
    public function show(string $uuid)
    {
        $project = Project::where('uuid', '=', $uuid)->first();
        return ProjectResource::make($project)->response()->setStatusCode(200);
    }

    /**
     * approve project project
     */
    #[Route(method: 'put', uri: '{uuid}/approve')]
    public function approve(string $uuid)
    {
        $project = Project::where('uuid', '=', $uuid)->first();

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->status = ProjectStatusEnum::APPROVE->value;
        $project->save();

        return $this->show($uuid);
    }
}
