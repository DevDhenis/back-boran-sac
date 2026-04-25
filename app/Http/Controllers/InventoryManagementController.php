<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestmentManagement\StoreInventoryRequest;
use App\Http\Requests\InvestmentManagement\UpdateInventoryRequest;
use App\Models\InventoryManagement;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InventoryManagementController extends Controller
{
    public function index(): JsonResponse
    {
        $query = InventoryManagement::with(['product', 'employee'])->orderByDesc('fecha_movimiento');

        if (request('product_id')) $query->where('product_id', request('product_id'));
        if (request('tipo_movimiento')) $query->where('tipo_movimiento', request('tipo_movimiento'));
        if (request('employee_id')) $query->where('employee_id', request('employee_id'));
        if (request('from')) $query->where('fecha_movimiento', '>=', request('from'));
        if (request('to')) $query->where('fecha_movimiento', '<=', request('to'));

        $perPage = (int) request('per_page', 15);
        $data = $query->paginate($perPage);

        return response()->json($data);
    }

    public function show(InventoryManagement $inventoryManagement): JsonResponse
    {
        return response()->json($inventoryManagement->load(['product', 'employee']));
    }

    public function store(StoreInventoryRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);

            $stockAntes = (float) $product->stock;
            $cantidad = (float) $request->cantidad;
            $tipo = $request->tipo_movimiento;

            if ($tipo === 'salida' && $cantidad > $stockAntes) {
                return response()->json(['message' => 'Stock insuficiente'], 422);
            }

            $stockDespues = match ($tipo) {
                'entrada' => $stockAntes + $cantidad,
                'salida' => $stockAntes - $cantidad,
                'ajuste' => $request->filled('stock_despues') ? (float) $request->stock_despues : $stockAntes,
            };

            $mov = InventoryManagement::create([
                'product_id' => $product->id,
                'employee_id' => $request->employee_id,
                'tipo_movimiento' => $tipo,
                'cantidad' => $cantidad,
                'motivo' => $request->motivo,
                'stock_antes' => $stockAntes,
                'stock_despues' => $stockDespues,
            ]);

            $product->stock = $stockDespues;
            $product->save();

            return response()->json($mov->load(['product', 'employee']), 201);
        });
    }

    public function update(UpdateInventoryRequest $request, InventoryManagement $inventoryManagement): JsonResponse
    {
        return DB::transaction(function () use ($request, $inventoryManagement) {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);

            $stockAntes = (float) $product->stock;
            $cantidad = (float) $request->cantidad;
            $tipo = $request->tipo_movimiento;

            if ($tipo === 'salida' && $cantidad > $stockAntes) {
                return response()->json(['message' => 'Stock insuficiente'], 422);
            }

            $stockDespues = match ($tipo) {
                'entrada' => $stockAntes + $cantidad,
                'salida' => $stockAntes - $cantidad,
                'ajuste' => $request->filled('stock_despues') ? (float) $request->stock_despues : $stockAntes,
            };

            $inventoryManagement->update([
                'product_id' => $product->id,
                'employee_id' => $request->employee_id,
                'tipo_movimiento' => $tipo,
                'cantidad' => $cantidad,
                'motivo' => $request->motivo,
                'stock_antes' => $stockAntes,
                'stock_despues' => $stockDespues,
            ]);

            $product->stock = $stockDespues;
            $product->save();

            return response()->json($inventoryManagement->load(['product', 'employee']));
        });
    }

    public function destroy(InventoryManagement $inventoryManagement): JsonResponse
    {
        return DB::transaction(function () use ($inventoryManagement) {
            if ($inventoryManagement->estado_registro === 'anulado') {
                return response()->json(['message' => 'Movimiento ya anulado'], 422);
            }

            $product = Product::lockForUpdate()->find($inventoryManagement->product_id);
            if ($product) {
                $product->stock = $inventoryManagement->stock_antes;
                $product->save();
            }

            $inventoryManagement->update(['estado_registro' => 'anulado']);

            return response()->json(['message' => 'Movimiento anulado correctamente']);
        });
    }
}