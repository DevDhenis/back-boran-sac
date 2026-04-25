<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProductPublicResource;

class ProductPublicController extends Controller
{
    public function index(): JsonResponse
    {
        $productos = Product::where('estado_registro', 'A') // solo productos activos
            ->where('stock', '>', 0) // solo productos con stock
            //->where('visible', true) // si usas campo visible/publicado
            ->with(['unit', 'category']) // relaciones necesarias para mostrar
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Catálogo público obtenido correctamente.',
            'data' => ProductPublicResource::collection($productos),
        ]);
    }
}