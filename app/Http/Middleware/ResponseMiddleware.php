<?php

namespace App\Http\Middleware;

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

            // Ambil data asli dari response
            $originalData = $response->getData(true);

            // Format response
            $formattedResponse = [
                'data' => $originalData,
            ];

            return response()->json($formattedResponse, $response->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], $response->getStatusCode());
        }
    }
}
