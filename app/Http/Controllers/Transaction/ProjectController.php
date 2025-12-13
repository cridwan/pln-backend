<?php

namespace App\Http\Controllers\Transaction;

use App\Data\NotificationData;
use App\Enums\AuthPermissionEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\RoleEnum;
use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Resources\ProjectResource;
use App\Models\Sequence;
use App\Models\Transaction\Project;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
class ProjectController extends Controller implements HasMiddleware
{
    protected $model = Project::class;
    protected array $search = [];
    protected array $with = ['inspectionType'];


    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
        ];
    }

    #[DoNotDiscover]
    public function __construct(public readonly NotificationService $notificationService)
    {
    }

    /**
     * list project
     */
    #[Route(method: 'get')]
    public function list(Request $request)
    {
        $query = Project::query()->with(['inspectionType.machine.unit.location', 'generateBy.user']);
        $searchColumn = ['name'];

        $query->when($request->filled('search'), function ($subQuery) use ($request, $searchColumn) {
            $subQuery->where(function ($search) use ($request, $searchColumn) {
                foreach ($searchColumn as $index => $item) {
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
        $query->when(auth()->user()->hasAnyRole(...RoleEnum::accessProject()), function ($query) {
            $query->whereHas('activities', fn($sub) => $sub->where('user_id', '=', auth()->user()->id));
        });

        $query->when($request->filled('filter'), function ($subQuery) use ($request) {
            $filter = explode(',', $request->filter);
            $subQuery->where($filter[0], $filter[1]);
        });

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * delete project
     */
    #[Route(method: 'delete', uri: 'project/destroy/{uuid}')]
    public function destroy(string $uuid)
    {
        $project = Project::where('uuid', '=', $uuid)->first();

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
        return ProjectResource::make($project->loadMissing(['inspectionType', 'approvedByUser']))->response()->setStatusCode(200);
    }

    /**
     * approve project project
     */
    #[Route(method: 'put', uri: '{uuid}/approve')]
    public function approve(Request $request, string $uuid)
    {
        $project = Project::where('uuid', '=', $uuid)->first();

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        $project->status = $project->status->updateStatus();
        $project->approved_at = $project->status == ProjectStatusEnum::PENDING ? null : now();
        $project->unapproved_at = $project->status == ProjectStatusEnum::APPROVE ? null : now();
        $project->approved_by = $request->user()->id;
        $project->reason = $request->reason;
        $project->save();

        return $this->show($uuid);
    }

    /**
     * request approve project project
     */
    #[Route(method: 'put', uri: '{uuid}/request-approve')]
    public function requestApprove(Request $request, string $uuid)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'uri' => 'required'
        ]);

        $project = Project::where('uuid', '=', $uuid)->first();

        if (!$project) {
            throw new BadRequestException('Project tidak ditemukan');
        }

        if ($project->status->isApprove()) {
            throw new BadRequestException('Project sudah disetujui');
        }

        $project->activities()->create([
            'user_id' => $request->user_id,
            'activity' => 'approval',
        ]);

        return $this->notificationService->store(new NotificationData(
            title: 'Request Approval Project',
            body: 'Anda memiliki permintaan approval project ' . $project->name,
            type: NotificationTypeEnum::REQUEST,
            receiver_id: $request->user_id,
            sender_id: $request->user()->id,
            uri: $request->uri,
            summary: 'Permintaan approval project dari ' . $request->user()->name,
        ));
    }

    #[Route(method: 'get', uri: 'sequences')]
    public function sequence()
    {
        $query = Sequence::query()->with('document');

        $builder = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::callback('inspectionType', function (Builder $query, string $value) {
                    $query->whereHas('inspections', fn($inspections) => $inspections->where('uuid', '=', $value));
                }),
                AllowedFilter::callback('additionalScope', function (Builder $query, string $value) {
                    $query->whereHas('scopes', fn($scopes) => $scopes->where('uuid', '=', $value));
                })
            ]);

        return $builder->get();
    }
}
