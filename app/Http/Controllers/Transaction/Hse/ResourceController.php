<?php

namespace App\Http\Controllers\Transaction\Hse;

use App\Core\Transaction\HseDocCore;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transaction Hse Resource')]
class ResourceController extends HseDocCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct()
    {
        $this->initCore();
    }
}
