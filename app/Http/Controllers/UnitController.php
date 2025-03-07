<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResponseMiddleware;
use App\Models\Unit;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Validation\Rule;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: [ResponseMiddleware::class])]
#[Group('Master Unit')]
class UnitController extends Controller
{
    use HasList, HasApiResource;

    protected $model = Unit::class;
    protected $search = ['name'];
    protected $with = ['location'];
    protected $rules = [];

    #[DoNotDiscover]
    public function __construct()
    {
        $this->rules = [
            'name' => ['required'],
            'location_uuid' => ['required', Rule::exists('masterdata.locations', 'uuid')]
        ];
    }
}
