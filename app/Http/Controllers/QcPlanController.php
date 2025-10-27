<?php

namespace App\Http\Controllers;

use App\Core\Master\QcPlanCore;
use App\Traits\InitCore;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

class QcPlanController extends QcPlanCore
{
    use InitCore;

    #[DoNotDiscover()]
    public function __construct()
    {
        $this->initCore();
    }
}
