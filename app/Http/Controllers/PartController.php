<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Traits\HasApiResource;
use App\Traits\HasList;

class PartController extends Controller
{
    use HasList, HasApiResource;

    protected $model = Part::class;
    protected array $search = ['name'];
    protected array $with = [];
    protected $rules = [];
}
