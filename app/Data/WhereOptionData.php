<?php

namespace App\Data;

class WhereOptionData
{
    public function __construct(
        public string $column,
        public string $operator,
        public string $value,
        public array $data,
        public string $boolean = 'and',
    ) {
    }
}
