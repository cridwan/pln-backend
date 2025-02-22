<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScopeStandartAdditionalRequest;
use App\Models\Transaction\AdditionalScope;
use App\Models\Transaction\ScopeStandartAsset;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction')]
class AdditionalScopeController extends Controller
{
    use HasPagination;

    protected $model = AdditionalScope::class;
    protected array $search = [];
    protected array $with = [];

    #[Route('POST')]
    public function asset(ScopeStandartAdditionalRequest $request)
    {
        return ScopeStandartAsset::create($request->all());
    }
}
