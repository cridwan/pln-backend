<?php

namespace App\Data;

class TemplateData
{
    public function __construct(
        public array $headers,
        public array|OptionData|null $options = null
    ) {
    }
}
