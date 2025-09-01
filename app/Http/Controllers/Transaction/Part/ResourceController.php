<?php

namespace App\Http\Controllers\Transaction\Part;

use App\Enums\AuthPermissionEnum;
use App\Enums\ConnectionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\ClonePartRequest;
use App\Models\PartStd;
use App\Models\Transaction\Part;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Part Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination, HasApiResource;

    protected $model = Part::class;
    protected array $search = ['no_drawing', 'name'];
    protected array $with = ['part', 'part.globalUnit'];

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
            'qty' => 'required',
            'noDrawing' => 'nullable',
            'note' => 'nullable',
            'global_unit_uuid' => ['required', Rule::exists('masterdata.global_units', 'uuid')],
            'project_uuid' => ['nullable', Rule::exists('transaction.projects', 'uuid')],
            'additional_scope_uuid' => 'nullable'
        ];
    }

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(ClonePartRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate part std
            PartStd::select('uuid', 'activity_uuid', 'part_uuid', 'qty')
                ->where('uuid', $request->part_uuid)
                ->each(function ($row) use ($request) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('part_stds');
                    $duplicate->activity_uuid = $request->activity_uuid;
                    $duplicate->save();
                });
        });

        return [
            'message' => 'Clone running successfully',
        ];
    }
}
