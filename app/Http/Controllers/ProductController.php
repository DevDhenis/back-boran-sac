<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\InventoryManagement;
use App\Models\Product;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with(['unit', 'category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de productos obtenida correctamente.',
            'data' => ProductResource::collection($products),
        ]);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Guarda la image según el driver (local en dev, Cloudinary en prod).
            $data['image'] = ImageUploader::upload($request->file('image'), 'products');
        }

        Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente.',
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Producto obtenido correctamente.',
            'data' => $product->load(['unit', 'category']),
        ]);
    }

    public function update(UpdateRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Reemplaza la image (local en dev, Cloudinary en prod).
            $data['image'] = ImageUploader::upload($request->file('image'), 'products');
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente.',
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente.',
        ]);
    }

    public function stockHistory(Product $product): JsonResponse
    {
        $movimientos = InventoryManagement::where('product_id', $product->id)
            ->orderByDesc('movement_date')
            ->with(['employee']) // si quieres incluir el empleado responsable
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Historial de stock obtenido correctamente.',
            'data' => $movimientos,
        ]);
    }
}
