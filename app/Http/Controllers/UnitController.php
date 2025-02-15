<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResponseMiddleware;
use App\Models\Unit;
use App\Traits\HasList;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
class UnitController extends Controller
{
    use HasList;

    protected $model = Unit::class;
    protected $search = [];
    protected $with = ['location'];
}
