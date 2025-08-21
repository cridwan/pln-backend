<?php

namespace App\Http\Controllers\Transaction\ConsumableMaterial;

use App\Enums\AuthPermissionEnum;
use App\Enums\ConnectionEnum;
use App\Exports\TransactionTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\CloneConsMatRequest;
use App\Models\ConsMatStd;
use App\Models\Transaction\ConsMat;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Transaction Consumable Material Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination, HasApiResource;

    protected $model = ConsMat::class;
    protected array $search = ['name', 'merk'];
    protected array $with = ['document', 'globalUnit'];
    protected $rules = [];

    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['pagination']),
        ];
    }

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

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(CloneConsMatRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate consumable material
            ConsMatStd::select('uuid', 'activity_uuid', 'cons_mat_uuid')
                ->where('uuid', $request->cons_mat_uuid)
                ->each(function ($row) use ($request) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('cons_mat_stds');
                    $duplicate->activity_uuid = $request->activity_uuid;
                    $duplicate->save();
                });
        });

        return [
            'message' => 'Clone running successfully',
        ];
    }
}
