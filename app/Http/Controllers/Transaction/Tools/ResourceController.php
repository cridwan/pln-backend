<?php

namespace App\Http\Controllers\Transaction\Tools;

use App\Enums\AuthPermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Tools;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transaction Tools Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasList, HasApiResource;

    protected $model = Tools::class;
    protected array $search = ['name'];
    protected array $with = ['globalUnit'];
    protected $rules = [];

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
        ];
    }

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => 'required',
            'qty' => 'required',
            'global_unit_uuid' => ['required', Rule::exists('masterdata.global_units', 'uuid')],
            'project_uuid' => ['required', Rule::exists('transaction.projects', 'uuid')],
            'section' => 'required'
        ];
    }
}
