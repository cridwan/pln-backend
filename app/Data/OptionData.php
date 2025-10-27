<?php

namespace App\Data;

class OptionData
{
    public function __construct(
        public string $column,
        public array $options
    ) {
    }
}
