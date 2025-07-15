<?php

namespace App\Response;

class ErrorResponse extends BaseResponse
{
    public function __construct(int $statusCode, string $message, public mixed $trace)
    {
        parent::__construct($statusCode, $message);
    }

    public function toJson()
    {
        return response()->json([
            'statusCode' => $this->getStatusCode(),
            'message' => mb_convert_encoding($this->getMessage(), 'UTF-8', 'UTF-8'),
            'trace' => env('APP_DEBUG', true) ?  $this->trace : [],
        ], $this->statusCode);
    }
}
