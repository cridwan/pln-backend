<?php

namespace App\Data;

use Illuminate\Http\Request;

/**
 * @property int $page
 * @property int $limit
 */
class PaginationData
{
    public function __construct(Request $request)
    {
        $this->page = $request->get("page", 1);
        $this->limit = $request->get("limit", 10);
    }
}
