<?php

namespace App\Http\Controllers\Transaction\Sequence;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Sequence;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;

#[Group(name: 'Transactio Sequence Resource')]
class ResourceController extends Controller
{
    use HasPagination;

    protected $model = Sequence::class;
    protected array $search = [];
    protected array $with = [];
}
