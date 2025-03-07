<?php

namespace App\Http\Controllers;

use App\Models\ConstMat;
use App\Traits\HasApiResource;
use App\Traits\HasList;

class ConsumbleMaterialController extends Controller
{
    use HasList, HasApiResource;

    protected $model = ConstMat::class;
    protected array $search = ['name'];
    protected array $with = [];
    protected $rules = [];
}
