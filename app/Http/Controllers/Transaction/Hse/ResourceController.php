<?php

namespace App\Http\Controllers\Transaction\Hse;

use App\Enums\AuthPermissionEnum;
use App\Enums\HseTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Hse;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transaction Hse Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination, HasApiResource;

    protected $model = Hse::class;
    protected array $search = ['title'];
    protected array $with = ['documents', 'document'];

    protected $rules = [];

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['pagination']),
        ];
    }

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'title' => 'required',
            'project_uuid' => ['required', Rule::exists('transaction.projects', 'uuid')],
            'type' => Rule::enum(HseTypeEnum::class)
        ];
    }
}
