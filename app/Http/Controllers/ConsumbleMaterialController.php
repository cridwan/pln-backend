<?php

namespace App\Http\Controllers;

use App\Models\ConsMat;
use App\Traits\HasApiResource;
use App\Traits\HasList;
use Dedoc\Scramble\Attributes\Group;

#[Group('Master Consumable Material')]
class ConsumbleMaterialController extends Controller
{
    use HasList, HasApiResource;

    protected $model = ConsMat::class;
    protected array $search = ['name'];
    protected array $with = [];
    protected $rules = [];
}
