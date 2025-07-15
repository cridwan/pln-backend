<?php

use App\Exceptions\AnauthenticateException;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationErrorException;
use App\Response\ErrorResponse;
use App\Response\ValidationErrorResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $th) {
            $statusCode = HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR;
            $message = $th->getMessage();
            $errors = null;
            $validationError = false;

            if ($th instanceof BadRequestException) {
                $statusCode = HttpFoundationResponse::HTTP_BAD_REQUEST;
            }

            if ($th instanceof UnauthorizedException) {
                $statusCode = HttpFoundationResponse::HTTP_FORBIDDEN;
            }

            if ($th instanceof AnauthenticateException) {
                $statusCode = HttpFoundationResponse::HTTP_UNAUTHORIZED;
            }

            if ($th instanceof NotFoundException) {
                $statusCode = HttpFoundationResponse::HTTP_NOT_FOUND;
            }

            if ($th instanceof ValidationErrorException) {
                $statusCode = HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY;
                $message = 'Validation errors';
                $errors = json_decode($th->getMessage());
                $validationError = true;
            }

            if ($th instanceof ValidationException) {
                $statusCode = HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY;
                $message = 'Validation errors';
                $errors = $th->errors();
                $validationError = true;
            }

            return $validationError ? (new ValidationErrorResponse($statusCode, $message, $errors))->toJson() : (new ErrorResponse($statusCode, $message, $th->getTrace()))->toJson();
        });
    })->create();
