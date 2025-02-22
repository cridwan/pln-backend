<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\ConstMat;
use App\Traits\HasApiResource;
use App\Traits\HasPagination;
use Dedoc\Scramble\Attributes\Group;

#[Group('Transaction')]
class ConsMatController extends Controller
{
    use HasPagination, HasApiResource;

    protected $model = ConstMat::class;
    protected array $search = [];
    protected array $with = [];
}
