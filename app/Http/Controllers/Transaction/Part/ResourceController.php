<?php

namespace App\Http\Controllers\Transaction\Part;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Part;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;

#[Group(name: 'Transaction Part Resource')]
class ResourceController extends Controller
{
    use HasPagination, HasApiResource;

    protected $model = Part::class;
    protected array $search = [];
    protected array $with = ['globalUnit'];
}
