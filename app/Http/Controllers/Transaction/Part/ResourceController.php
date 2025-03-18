<?php

namespace App\Http\Controllers\Transaction\Part;

use App\Enums\AuthPermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Part;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transaction Part Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination, HasApiResource;

    protected $model = Part::class;
    protected array $search = ['no_drawing', 'name'];
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
            'noDrawing' => 'nullable',
            'note' => 'nullable',
            'global_unit_uuid' => ['required', Rule::exists('masterdata.global_units', 'uuid')],
            'project_uuid' => ['nullable', Rule::exists('transaction.projects', 'uuid')],
            'additional_scope_uuid' => 'nullable'
        ];
    }
}
