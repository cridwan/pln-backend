<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResponseMiddleware;
use App\Models\Machine;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
class MachineController extends Controller
{
    use HasList, HasApiResource;

    protected $model = Machine::class;
    protected $search = ['name', 'unit.name'];
    protected $with = ['unit'];
    protected $rules = [];

    public function __construct()
    {
        $this->rules = [
            'name' => ['required'],
            'unit_uuid' => ['required', Rule::exists('masterdata.units', 'uuid')]
        ];
    }
}
