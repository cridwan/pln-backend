<?php

namespace App\Http\Controllers\Transaction\ConsumableMaterial;

use App\Http\Controllers\Controller;
use App\Models\Transaction\ConsMat;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

#[Group('Transaction Consumable Material Resource')]
class ResourceController extends Controller
{
    use HasPagination, HasApiResource;

    protected $model = ConsMat::class;
    protected array $search = ['name', 'merk'];
    protected array $with = ['document', 'globalUnit'];
    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => 'required',
            'merk' => 'nullable',
            'qty' => 'required',
            'global_unit_uuid' => ['required', Rule::exists('masterdata.global_units', 'uuid')],
            'project_uuid' => 'nullable',
            'additional_scope_uuid' => 'nullable'
        ];
    }
}
