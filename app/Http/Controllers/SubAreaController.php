<?php

namespace App\Http\Controllers;

use App\Core\Master\SubAreaCore;
use App\Traits\InitCore;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

class SubAreaController extends SubAreaCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
