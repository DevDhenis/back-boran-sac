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
        $query = InventoryManagement::with(['product', 'employee'])->orderByDesc('movement_date');

        if (request('product_id')) {
            $query->where('product_id', request('product_id'));
        }
        if (request('movement_type')) {
            $query->where('movement_type', request('movement_type'));
        }
        if (request('employee_id')) {
            $query->where('employee_id', request('employee_id'));
        }
        if (request('from')) {
            $query->where('movement_date', '>=', request('from'));
        }
        if (request('to')) {
            $query->where('movement_date', '<=', request('to'));
        }

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

            $stockBefore = (float) $product->stock;
            $quantity = (float) $request->quantity;
            $type = $request->movement_type;

            if ($type === 'outbound' && $quantity > $stockBefore) {
                return response()->json(['message' => 'Stock insuficiente'], 422);
            }

            $stockAfter = match ($type) {
                'inbound' => $stockBefore + $quantity,
                'outbound' => $stockBefore - $quantity,
                'adjustment' => $request->filled('stock_after') ? (float) $request->stock_after : $stockBefore,
            };

            $movement = InventoryManagement::create([
                'product_id' => $product->id,
                'employee_id' => $request->employee_id,
                'movement_type' => $type,
                'quantity' => $quantity,
                'reason' => $request->reason,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);

            $product->stock = $stockAfter;
            $product->save();

            return response()->json($movement->load(['product', 'employee']), 201);
        });
    }

    public function update(UpdateInventoryRequest $request, InventoryManagement $inventoryManagement): JsonResponse
    {
        return DB::transaction(function () use ($request, $inventoryManagement) {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);

            $stockBefore = (float) $product->stock;
            $quantity = (float) $request->quantity;
            $type = $request->movement_type;

            if ($type === 'outbound' && $quantity > $stockBefore) {
                return response()->json(['message' => 'Stock insuficiente'], 422);
            }

            $stockAfter = match ($type) {
                'inbound' => $stockBefore + $quantity,
                'outbound' => $stockBefore - $quantity,
                'adjustment' => $request->filled('stock_after') ? (float) $request->stock_after : $stockBefore,
            };

            $inventoryManagement->update([
                'product_id' => $product->id,
                'employee_id' => $request->employee_id,
                'movement_type' => $type,
                'quantity' => $quantity,
                'reason' => $request->reason,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);

            $product->stock = $stockAfter;
            $product->save();

            return response()->json($inventoryManagement->load(['product', 'employee']));
        });
    }

    public function destroy(InventoryManagement $inventoryManagement): JsonResponse
    {
        return DB::transaction(function () use ($inventoryManagement) {
            if ($inventoryManagement->status === 'voided') {
                return response()->json(['message' => 'Movimiento ya anulado'], 422);
            }

            $product = Product::lockForUpdate()->find($inventoryManagement->product_id);
            if ($product) {
                $product->stock = $inventoryManagement->stock_before;
                $product->save();
            }

            $inventoryManagement->update(['status' => 'voided']);

            return response()->json(['message' => 'Movimiento anulado correctamente']);
        });
    }
}
