<?php

namespace App\Http\Middleware;

use Closure;

class CheckAccess
{
  public function handle($request, Closure $next, $requiredAccess)
  {
    $user = auth()->user();

    if (!$user || !$user->role) {
      return response()->json(['message' => 'No autorizado'], 403);
    }

    $hasAccess = $user->role
      ->accesses
      ->pluck('nombre')
      ->map(fn($n) => strtolower($n))
      ->contains(strtolower($requiredAccess));

    if (!$hasAccess) {
      return response()->json(['message' => 'No autorizado'], 403);
    }

    return $next($request);
  }
}
