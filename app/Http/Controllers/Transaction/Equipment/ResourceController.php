<?php

namespace App\Http\Controllers\Transaction\Equipment;

use App\Core\Transaction\EquipmentCore;
use App\Data\PaginationData;
use App\Data\WhereOptionData;
use App\Enums\ConnectionEnum;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\Transaction\CloneEquipmentRequest;
use App\Models\Activity;
use App\Models\ConsMatStd;
use App\Models\Equipment as ModelsEquipment;
use App\Models\ManpowerStd;
use App\Models\PartStd;
use App\Models\Transaction\Equipment;
use App\Models\Transaction\ScopeStandart;
use App\Services\GenerateService;
use App\Traits\InitCore;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group(name: 'Transaction Equipments Resource')]
class ResourceController extends EquipmentCore
{
    use InitCore;

    #[DoNotDiscover]
    public function __construct(public GenerateService $generateService)
    {
        $this->initCore();
    }

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(CloneEquipmentRequest $request)
    {
        $equipment = Equipment::where('uuid', $request->equipment_uuid)->first();

        if ($equipment) {
            throw new BadRequestException('Data ' . $equipment->name . ' sudah dilakukan cloning');
        }

        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate equipment
            $this->generateService->cloneEquipment(new WhereOptionData(
                'uuid',
                '=',
                $request->equipment_uuid,
                [
                    'scope_standart_uuid' => $request->scope_standart_uuid
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

        $equipment = ModelsEquipment::query()
            ->doestHaveTransaction($request->input('inspection_type_uuid', null), $request->input('scope_standart_uuid', null))
            ->paginate($pagination->limit, ['*'], 'page', $pagination->page);

        return $equipment;
    }
}
