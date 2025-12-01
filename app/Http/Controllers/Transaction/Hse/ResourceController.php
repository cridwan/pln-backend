<?php

namespace App\Http\Controllers\Transaction\Hse;

use App\Core\Transaction\HseDocCore;
use App\Data\PaginationData;
use App\Data\WhereOptionData;
use App\Enums\ConnectionEnum;
use App\Http\Requests\CloneHseDocRequest;
use App\Models\HseDoc;
use App\Services\GenerateService;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Hse Resource')]
class ResourceController extends HseDocCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct(public GenerateService $generateService)
    {
        $this->initCore();
    }

    /**
     * data for options select
     */
    #[Route(method: 'get', uri: 'select/options')]
    public function select(Request $request)
    {
        $pagination = new PaginationData($request);

        $equipment = HseDoc::query()
            ->doestHaveTransaction($request->input('project_uuid', null))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $equipment;
    }

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(CloneHseDocRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate consumable material
            $this->generateService->cloneHseDocSpesific(new WhereOptionData(
                'uuid',
                '=',
                $request->uuid,
                [
                    'project_uuid' => $request->project_uuid
                ]
            ));
        });

        return [
            'message' => 'Clone running successfully',
        ];
    }
}
