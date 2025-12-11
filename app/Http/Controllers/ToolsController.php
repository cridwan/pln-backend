<?php

namespace App\Http\Controllers;

use App\Core\Master\ToolsCore;
use App\Http\Middleware\ResponseMiddleware;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Master Tools')]
class ToolsController extends ToolsCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
