<?php

namespace App\Http\Controllers;

use App\Models\Access;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccessController extends Controller
{
    public function index(): JsonResponse
    {
        $accesses = Access::with('children')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de accesos obtenida correctamente.',
            'data' => $accesses,
        ]);
    }
}
