<?php

namespace App\Http\Controllers\Transaction\Manpower;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Manpower;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transaction Manpower Resource')]
class ResourceController extends Controller
{
    use HasPagination, HasApiResource;

    protected $model = Manpower::class;
    protected array $search = [];
    protected array $with = [];

    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => 'required',
            'type' => 'required',
            'qty' => 'required',
            'note' => 'nullable',
            'project_uuid' => 'nullable',
            'additional_scope_uuid' => 'nullable'
        ];
    }
}
