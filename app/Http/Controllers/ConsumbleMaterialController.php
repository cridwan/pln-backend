<?php

namespace App\Http\Controllers;

use App\Core\Master\ConsumableMaterialCore;
use App\Http\Middleware\ResponseMiddleware;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Master Consumable Material')]
#[Route(middleware: ResponseMiddleware::class)]
class ConsumbleMaterialController extends ConsumableMaterialCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
