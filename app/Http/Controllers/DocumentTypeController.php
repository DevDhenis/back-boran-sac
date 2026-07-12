<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\JsonResponse;

class DocumentTypeController extends Controller
{
    /**
     * Lista de tipos de documento activos para poblar selects (id + name).
     */
    public function index(): JsonResponse
    {
        $documentTypes = DocumentType::where('status', 'A')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'message' => 'Tipos de documento obtenidos correctamente.',
            'data' => $documentTypes,
        ]);
    }
}
