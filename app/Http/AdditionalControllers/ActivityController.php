<?php

namespace App\Http\AdditionalControllers;

use App\Core\Master\Detail\ActivityCore;
use App\Http\Middleware\ResponseMiddleware;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: '(Additional) Master Activity')]
class ActivityController extends ActivityCore
{
    use InitCore;
    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
