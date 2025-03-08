<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\GenerateRequest;
use App\Models\Transaction\Project;
use App\Services\GenerateService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Generate', description: 'Generate API Documentation')]
#[Route(middleware: ResponseMiddleware::class)]
class GenerateController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
        ];
    }

    #[DoNotDiscover]
    public function __construct(
        public GenerateService $generateService
    ) {}


    #[Route(method: 'post', name: 'generate.index')]
    public function index(GenerateRequest $request): Project
    {
        return $this->generateService->generate($request);
    }
}
