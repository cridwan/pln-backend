<?php

namespace App\Http\Controllers\Transaction\ConsumableMaterial;

use App\Enums\AuthPermissionEnum;
use App\Exports\TransactionTemplateExport;
use App\Http\Controllers\Controller;
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
     * import template
     */
    #[Route('post', 'export')]
    public function export()
    {
        return (new TransactionTemplateExport(
            [
                'uuid',
                'name',
                'merk',
                'global_unit.name',
                'qty',
            ],
            ConsMat::query()
                ->with('globalUnit')
        ))->download('consmat-template-' . date('YmdHis') . '.xlsx');
    }
}
