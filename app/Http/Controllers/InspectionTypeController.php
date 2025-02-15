<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResponseMiddleware;
use App\Models\InspectionType;
use App\Traits\HasList;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
class InspectionTypeController extends Controller
{
    use HasList;

    protected $model = InspectionType::class;
    protected array $search = [];
    protected array $with = [];
}
