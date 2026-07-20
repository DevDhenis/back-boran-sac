<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $categories = ProductCategory::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Categorías obtenidas correctamente.',
                'data' => ProductCategoryResource::collection($categories),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $category = ProductCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada correctamente.',
            'data' => new ProductCategoryResource($category),
        ], 201);
    }

    public function show(ProductCategory $productCategory): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Categoría obtenida correctamente.',
            'data' => new ProductCategoryResource($productCategory),
        ]);
    }

    public function update(Request $request, ProductCategory $productCategory): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $productCategory->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada.',
            'data' => new ProductCategoryResource($productCategory),
        ]);
    }

    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        $productCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada.',
        ]);
    }
}
