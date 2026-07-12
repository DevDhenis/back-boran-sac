<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;

class CheckAccess
{
    public function handle($request, Closure $next, $requiredAccess)
    {
        $user = auth()->user();

        if (! $user || ! $user->role) {
            return ApiResponse::forbidden();
        }

        $hasAccess = $user->role
            ->accesses
            ->pluck('name')
            ->map(fn ($n) => strtolower($n))
            ->contains(strtolower($requiredAccess));

        if (! $hasAccess) {
            return ApiResponse::forbidden();
        }

        return $next($request);
    }
}
