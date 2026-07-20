<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierReturn\StoreSupplierReturnRequest;
use App\Http\Resources\SupplierReturnResource;
use App\Models\InventoryManagement;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SupplierReturnController extends Controller
{
    private const EAGER = ['supplier', 'employee.person', 'items.product'];

    public function index(): JsonResponse
    {
        $returns = SupplierReturn::with(self::EAGER)->orderByDesc('return_date')->orderByDesc('id')->get();

        return response()->json([
            'success' => true,
            'message' => 'Devoluciones a proveedor obtenidas correctamente.',
            'data' => SupplierReturnResource::collection($returns),
        ]);
    }

    /**
     * Register a supplier return: validates against purchases (can't return more than
     * bought minus already returned) and current stock, then posts outbound movements.
     */
    public function store(StoreSupplierReturnRequest $request): JsonResponse
    {
        $supplierId = (int) $request->supplier_id;
        $items = collect($request->items);
        $productIds = $items->pluck('product_id');

        // Bought from this supplier vs already returned to it, per product.
        $purchased = PurchaseItem::whereHas('purchase', fn ($q) => $q->where('supplier_id', $supplierId))
            ->whereIn('product_id', $productIds)
            ->selectRaw('product_id, SUM(quantity) as qty')->groupBy('product_id')->pluck('qty', 'product_id');
        $returned = SupplierReturnItem::whereHas('supplierReturn', fn ($q) => $q->where('supplier_id', $supplierId))
            ->whereIn('product_id', $productIds)
            ->selectRaw('product_id, SUM(quantity) as qty')->groupBy('product_id')->pluck('qty', 'product_id');

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            $qty = (float) $item['quantity'];
            $available = (float) ($purchased[$item['product_id']] ?? 0) - (float) ($returned[$item['product_id']] ?? 0);

            if ($qty > $available) {
                return response()->json([
                    'success' => false,
                    'message' => "Solo puedes devolver {$available} unidad(es) de \"{$product?->name}\" a este proveedor (segun lo comprado).",
                ], 422);
            }
            if ($qty > (float) ($product?->stock ?? 0)) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente para devolver \"{$product?->name}\".",
                ], 422);
            }
        }

        $supplierReturn = DB::transaction(function () use ($request, $supplierId, $items) {
            $employeeId = auth()->user()->employee->id ?? null;

            $supplierReturn = SupplierReturn::create([
                'supplier_id' => $supplierId,
                'employee_id' => $employeeId,
                'return_date' => now(),
                'reason' => $request->reason,
                'credit_status' => 'pending',
            ]);

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (float) $item['quantity'];

                SupplierReturnItem::create([
                    'supplier_return_id' => $supplierReturn->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                ]);

                $stockBefore = (float) $product->stock;
                $stockAfter = $stockBefore - $qty;

                InventoryManagement::create([
                    'product_id' => $product->id,
                    'supplier_id' => $supplierId,
                    'supplier_return_id' => $supplierReturn->id,
                    'employee_id' => $employeeId,
                    'movement_type' => 'outbound',
                    'origin' => 'supplier_return',
                    'quantity' => $qty,
                    'reason' => "Devolución a proveedor #{$supplierReturn->id}",
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'movement_date' => now(),
                ]);

                $product->stock = $stockAfter;
                $product->save();
            }

            return $supplierReturn;
        });

        $supplierReturn->load(self::EAGER);

        return response()->json([
            'success' => true,
            'message' => 'Devolución a proveedor registrada. El stock se descontó.',
            'data' => new SupplierReturnResource($supplierReturn),
        ], 201);
    }

    /**
     * Register the supplier's credit note for a return (financial step, separate).
     */
    public function credit(SupplierReturn $supplierReturn): JsonResponse
    {
        if ($supplierReturn->credit_status === 'credited') {
            return response()->json(['success' => false, 'message' => 'Esta devolución ya tiene nota de crédito.'], 422);
        }

        $supplierReturn->update(['credit_status' => 'credited']);
        $supplierReturn->load(self::EAGER);

        return response()->json([
            'success' => true,
            'message' => 'Nota de crédito registrada correctamente.',
            'data' => new SupplierReturnResource($supplierReturn),
        ]);
    }
}
