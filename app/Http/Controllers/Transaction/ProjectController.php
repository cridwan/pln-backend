<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\AuthPermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
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
            new Middleware(AuthPermissionEnum::AUTH_API->value),
        ];
    }
}
