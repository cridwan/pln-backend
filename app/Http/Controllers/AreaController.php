<?php

namespace App\Http\Controllers;

use App\Core\Master\AreaCore;
use App\Traits\InitCore;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

class AreaController extends AreaCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
