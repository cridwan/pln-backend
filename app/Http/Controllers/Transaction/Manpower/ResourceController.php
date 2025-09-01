<?php

namespace App\Http\Controllers\Transaction\Manpower;

use App\Enums\AuthPermissionEnum;
use App\Enums\ConnectionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\CloneManpowerRequest;
use App\Models\ManpowerStd;
use App\Models\Transaction\Manpower;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group(name: 'Transaction Manpower Resource')]
class ResourceController extends Controller implements HasMiddleware
{
    use HasPagination, HasApiResource;

    protected $model = Manpower::class;
    protected array $search = ['name'];
    protected array $with = [];

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
            'type' => 'required',
            'qty' => 'required',
            'note' => 'nullable',
            'project_uuid' => 'nullable',
            'additional_scope_uuid' => 'nullable'
        ];
    }

    /**
     * clone data
     */
    #[Route(method: 'post')]
    public function clone(CloneManpowerRequest $request)
    {
        DB::connection(ConnectionEnum::TRANSACTION->value)->transaction(function () use ($request) {
            // duplicate manpower std
            ManpowerStd::select('uuid', 'activity_uuid', 'manpower_uuid', 'qty')
                ->where('uuid', $request->manpower_uuid)
                ->each(function ($row) use ($request) {
                    $duplicate = $row->replicate();
                    $duplicate->setConnection(ConnectionEnum::TRANSACTION->value);
                    $duplicate->setTable('manpower_stds');
                    $duplicate->activity_uuid = $request->activity_uuid;
                    $duplicate->save();
                });
        });

        return [
            'message' => 'Clone running successfully',
        ];
    }
}
