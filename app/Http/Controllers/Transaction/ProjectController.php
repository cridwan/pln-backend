<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\Transaction\Project;
use App\Traits\HasList;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
class ProjectController extends Controller
{
    use HasList;

    protected $model = Project::class;
    protected array $search = [];
    protected array $with = ['inspectionType'];
}
