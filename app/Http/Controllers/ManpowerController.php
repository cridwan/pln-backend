<?php

namespace App\Http\Controllers;

use App\Core\Master\ManpowerCore;
use App\Http\Middleware\ResponseMiddleware;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Master Manpower')]
#[Route(middleware: ResponseMiddleware::class)]
class ManpowerController extends ManpowerCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
