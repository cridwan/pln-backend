<?php

namespace App\Response;

class BaseResponse
{
    public function __construct(
        public int $statusCode,
        public string $message,
    ) {}

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
