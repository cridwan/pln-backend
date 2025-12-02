<?php

namespace App\Http\Controllers\Transaction\AdditionalScope;

use App\Core\Transaction\AdditionalScopeCore;
use App\Data\PaginationData;
use App\Data\WhereOptionData;
use App\Enums\ConnectionEnum;
use App\Http\Requests\ScopeStandartAdditionalRequest;
use App\Http\Requests\Transaction\CloneAdditionalScopeRequest;
use App\Models\AdditionalScope;
use App\Models\Transaction\ScopeStandartAsset;
use App\Services\GenerateService;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Additional Scope Resources')]
class ResourceController extends AdditionalScopeCore
{
    use InitCore;

    public function __construct(public GenerateService $generateService)
    {
        $this->initCore();
    }

    /**
     * store asset
     */
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

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(CloneAdditionalScopeRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate additional scope
            $this->generateService->cloneAdditionalScope(new WhereOptionData(
                'uuid',
                '=',
                $request->additional_scope_uuid,
                [
                    'project_uuid' => $request->project_uuid
                ]
            ));
        });

        return [
            'message' => 'Clone running successfully',
        ];
    }

    /**
     * data for options select
     */
    #[Route(method: 'get', uri: 'select/options')]
    public function select(Request $request)
    {
        $pagination = new PaginationData($request);

        $scopes = AdditionalScope::query()
            ->doestHaveTransaction($request->input('inspection_type_uuid', null))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $scopes;
    }
}
