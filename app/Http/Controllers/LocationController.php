<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResponseMiddleware;
use App\Models\Location;
use App\Traits\HasList;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'location')]
class LocationController extends Controller
{
    use HasList;

    protected $model = Location::class;
    protected array $search = [];
    protected array $with = [];
}
