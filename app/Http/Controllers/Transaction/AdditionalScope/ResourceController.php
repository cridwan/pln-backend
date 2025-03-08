<?php

namespace App\Http\Controllers\Transaction\AdditionalScope;

use App\Enums\AuthPermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScopeStandartAdditionalRequest;
use App\Models\Transaction\AdditionalScope;
use App\Models\Transaction\ScopeStandartAsset;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Additional Scope Resources')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination;

    protected $model = AdditionalScope::class;
    protected array $search = [];
    protected array $with = ['assetWelnes.document', 'ohRecom.document', 'woPriority.document', 'history.document', 'rla.document', 'ncr.document', 'sequenceAnimation'];

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value),
        ];
    }

    #[Route('POST')]
    public function asset(ScopeStandartAdditionalRequest $request)
    {
        $exist = ScopeStandartAsset::where('scope_standart_uuid', $request->scope_standart_uuid)->where('category', $request->category)->first();

        if ($exist) {
            ScopeStandartAsset::where('scope_standart_uuid', $request->scope_standart_uuid)
                ->where('category', $request->category)
                ->update($request->all());
            return $exist;
        }
        return ScopeStandartAsset::create($request->all());
    }
}
