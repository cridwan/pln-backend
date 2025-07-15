<?php

namespace App\Http\Middleware;

use App\Response\SuccessResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class ResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            // Jika response bukan instance JsonResponse, jangan ubah
            if (!$response instanceof JsonResponse) {
                return $response;
            }

            if (
                $response instanceof JsonResponse &&
                $response->isSuccessful()
            ) {
                // Ambil data asli dari response
                $originalData = $response->getData(true);

                return (new SuccessResponse($response->getStatusCode(), 'Success', $originalData))->toJson();
            }

            return $response;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
