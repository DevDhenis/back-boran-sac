<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCategoryPublicResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;

class ProductCategoryPublicController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ProductCategory::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Categorías públicas obtenidas correctamente.',
            'data' => ProductCategoryPublicResource::collection($categories),
        ]);
    }
}
