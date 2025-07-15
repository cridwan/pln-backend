<?php

namespace App\Response;

class SuccessResponse extends BaseResponse
{
    public function __construct(int $statusCode, string $message, public mixed $data)
    {
        parent::__construct($statusCode, $message);
    }

    public function toJson()
    {
        return response()->json([
            'statusCode' => $this->getStatusCode(),
            'message' => mb_convert_encoding($this->getMessage(), 'UTF-8', 'UTF-8'),
            'data' => $this->data,
        ], $this->statusCode);
    }
}
