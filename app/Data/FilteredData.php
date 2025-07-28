<?php

namespace App\Data;

class FilteredData
{
    private array $groups = [
        "AND",
        "OR",
    ];

    public function __construct(public array $data) {}

    public function validate() {}
}
