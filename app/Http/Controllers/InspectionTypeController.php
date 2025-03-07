<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResponseMiddleware;
use App\Models\InspectionType;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
class InspectionTypeController extends Controller
{
    use HasList, HasApiResource;

    protected $model = InspectionType::class;
    protected array $search = ['name'];
    protected array $with = [];
    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => ['required'],
            'machine_uuid' => ['required', Rule::exists('masterdata.machines', 'uuid')]
        ];
    }
}
