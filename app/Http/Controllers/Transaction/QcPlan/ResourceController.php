<?php

namespace App\Http\Controllers\Transaction\QcPlan;

use App\Core\Transaction\QcPlanCore;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transaction Qc Plan Resource')]
class ResourceController extends QcPlanCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
