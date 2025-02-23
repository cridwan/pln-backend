<?php

namespace App\Http\Controllers\Transaction\ScopeStandart;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScopeStandartRequest;
use App\Models\Transaction\ScopeStandart;
use App\Models\Transaction\ScopeStandartAsset;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Scope Standart Resource')]
class ResourceController extends Controller
{
    use HasPagination;

    protected $model = ScopeStandart::class;
    protected array $search = [];
    protected array $with = ['details', 'assetWelnes.document', 'ohRecom.document', 'woPriority.document', 'history.document', 'rla.document', 'ncr.document'];

    #[Route('POST')]
    public function asset(ScopeStandartRequest $request)
    {
        return ScopeStandartAsset::create($request->all());
    }
}
