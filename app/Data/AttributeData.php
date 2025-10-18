<?php

namespace App\Data;

class AttributeData
{
    public function __construct(
        public mixed $key,
        public string $label,
    ) {
    }
}
