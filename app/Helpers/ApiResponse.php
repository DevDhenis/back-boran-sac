<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Respuesta de error estándar del proyecto: { success:false, message }.
     */
    public static function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Respuesta de acceso denegado (403) con el contrato estándar: { success:false, message }.
     */
    public static function forbidden(string $message = 'No autorizado'): JsonResponse
    {
        return self::error($message, 403);
    }
}