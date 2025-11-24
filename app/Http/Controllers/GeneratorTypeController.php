<?php

namespace App\Http\Controllers;

use App\Core\Master\GeneratorTypeCore;
use App\Traits\InitCore;
use Illuminate\Http\Request;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

class GeneratorTypeController extends GeneratorTypeCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
