<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil API key dari header request (misal: X-API-KEY)
        $apiKey = $request->header('X-API-KEY');

        // Cek apakah API key cocok dengan yang ada di file .env
        if (!$apiKey || $apiKey !== env('INTERNAL_API_KEY')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: API Key tidak valid atau tidak ditemukan'
            ], 401);
        }

        return $next($request);
    }
}
