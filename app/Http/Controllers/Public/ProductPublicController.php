<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductPublicResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductPublicController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::where('status', 'A') // solo productos activos
            ->where('stock', '>', 0) // solo productos con stock
            // ->where('visible', true) // si usas campo visible/publicado
            ->with(['unit', 'category']) // relaciones necesarias para mostrar
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Catálogo público obtenido correctamente.',
            'data' => ProductPublicResource::collection($products),
        ]);
    }
}
