<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return ApiResponse::error('El token es inválido', 401);
            } elseif ($e instanceof TokenExpiredException) {
                return ApiResponse::error('El token expiró', 401);
            } else {
                return ApiResponse::error('Token no encontrado', 401);
            }
        }

        return $next($request);
    }
}
