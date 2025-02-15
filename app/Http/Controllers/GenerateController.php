<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateRequest;
use App\Models\Transaction\Project;
use App\Services\GenerateService;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Generate', description: 'Generate API Documentation')]
class GenerateController extends Controller
{
    public function __construct(
        public GenerateService $generateService
    ) {}


    #[Route(method: 'post', name: 'generate.index')]
    public function index(GenerateRequest $request): Project
    {
        return $this->generateService->generate($request);
    }
}
