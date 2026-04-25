<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProductCategoryPublicResource;

class ProductCategoryPublicController extends Controller
{
    public function index(): JsonResponse
    {
        $categorias = ProductCategory::orderBy('nombre')->get();

        return response()->json([
            'success' => true,
            'message' => 'Categorías públicas obtenidas correctamente.',
            'data' => ProductCategoryPublicResource::collection($categorias),
        ]);
    }
}