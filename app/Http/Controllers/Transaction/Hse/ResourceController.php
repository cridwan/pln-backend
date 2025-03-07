<?php

namespace App\Http\Controllers\Transaction\Hse;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Hse;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transaction Hse Resource')]
class ResourceController extends Controller
{
    use HasPagination;

    protected $model = Hse::class;
    protected array $search = ['title'];
    protected array $with = ['document'];

    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'title' => 'required',
            'project_uuid' => ['required', Rule::exists('transaction.projects', 'uuid')],
        ];
    }
}
