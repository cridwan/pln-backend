<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResponseMiddleware;
use App\Models\Machine;
use App\Traits\HasList;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
class MachineController extends Controller
{
    use HasList;

    protected $model = Machine::class;
    protected $search = ['name', 'unit.name'];
    protected $with = ['unit'];
}
