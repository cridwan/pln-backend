<?php

namespace App\Http\Controllers\Transaction\Sequence;

use App\Enums\AuthPermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Transaction\Sequence;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transactio Sequence Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination;

    protected $model = Sequence::class;
    protected array $search = [];
    protected array $with = [];

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['list', 'show', 'index', 'pagination']),
            new Middleware(
                RoleMiddleware::using(
                    RoleEnum::transactionRole()
                ),
                except: ['list', 'show', 'index', 'pagination']
            )
        ];
    }
}
