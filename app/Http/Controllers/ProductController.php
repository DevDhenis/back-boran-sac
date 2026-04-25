<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\InventoryManagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('products', 'public');
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

        if ($request->hasFile('imagen')) {
            if ($product->imagen && Storage::disk('public')->exists($product->imagen)) {
                Storage::disk('public')->delete($product->imagen);
            }

            $data['imagen'] = $request->file('imagen')->store('products', 'public');
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
            ->orderByDesc('fecha_movimiento')
            ->with(['employee']) // si quieres incluir el empleado responsable
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Historial de stock obtenido correctamente.',
            'data' => $movimientos,
        ]);
    }
}
