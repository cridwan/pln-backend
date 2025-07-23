<?php

namespace App\Http\Controllers\Transaction\QcPlan;

use App\Enums\AuthPermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Transaction\QcPlan;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transaction Qc Plan Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination;

    protected $model = QcPlan::class;
    protected array $search = [];
    protected array $with = ['document'];

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['pagination']),
        ];
    }
}
