<?php

namespace App\Http\Controllers\Transaction\SequenceAnimation;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SequenceAnimation;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group(name: 'Transaction Sequence Animation Resource')]
class ResourceController extends Controller
{
    use HasList, HasApiResource;

    protected $model = SequenceAnimation::class;
    protected array $search = [];
    protected array $with = [];
    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => 'required',
            'slug' => 'required',
            'additonal_scope_uuid' => ['required', Rule::exists('transaction.additional_scopes', 'uuid')],
        ];
    }
}
