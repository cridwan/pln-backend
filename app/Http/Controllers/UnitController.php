<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResponseMiddleware;
use App\Models\Unit;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
class UnitController extends Controller
{
    use HasList, HasApiResource;

    protected $model = Unit::class;
    protected $search = [];
    protected $with = ['location'];
    protected $rules = [];

    public function __construct()
    {
        $this->rules = [
            'name' => ['required'],
            'location_uuid' => ['required', Rule::exists('masterdata.locations', 'uuid')]
        ];
    }
}
