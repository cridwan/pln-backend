<?php

namespace App\Http\Controllers\Transaction\ScopeStandart;

use App\Enums\ScopeStandartTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScopeStandartRequest;
use App\Models\Transaction\ScopeStandart;
use App\Models\Transaction\ScopeStandartAsset;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Scope Standart Resource')]
class ResourceController extends Controller
{
    use HasPagination, HasApiResource;

    protected $model = ScopeStandart::class;
    protected array $search = [];
    protected array $with = ['details', 'assetWelnes.document', 'ohRecom.document', 'woPriority.document', 'history.document', 'rla.document', 'ncr.document'];
    protected $rules = [];

    public function __construct()
    {
        $this->rules = [
            'name' => 'required',
            'category' => ['required', Rule::enum(ScopeStandartTypeEnum::class)],
            'project_uuid' => 'nullable',
            'additional_scope_uuid' => 'nullable'
        ];
    }

    #[Route('POST')]
    public function asset(ScopeStandartRequest $request)
    {
        return ScopeStandartAsset::create($request->all());
    }
}
