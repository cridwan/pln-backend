<?php

namespace App\Http\Controllers;

use App\Core\Master\PartCore;
use App\Http\Middleware\ResponseMiddleware;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Master Part')]
#[Route(middleware: ResponseMiddleware::class)]
class PartController extends PartCore
{
    use InitCore;
    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
