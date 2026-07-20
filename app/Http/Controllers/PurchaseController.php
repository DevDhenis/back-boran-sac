<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Resources\SupplierPurchaseResource;
use App\Models\InventoryManagement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    private const EAGER = ['supplier', 'employee.person', 'items.product'];

    public function index(): JsonResponse
    {
        $purchases = Purchase::with(self::EAGER)->orderByDesc('purchase_date')->orderByDesc('id')->get();

        return response()->json([
            'success' => true,
            'message' => 'Compras obtenidas correctamente.',
            'data' => SupplierPurchaseResource::collection($purchases),
        ]);
    }

    public function show(Purchase $purchase): JsonResponse
    {
        $purchase->load(self::EAGER);

        return response()->json([
            'success' => true,
            'message' => 'Compra obtenida correctamente.',
            'data' => new SupplierPurchaseResource($purchase),
        ]);
    }

    /**
     * Register a purchase: creates the document, its items, and the traceable
     * inbound movements (origin=purchase) that increase stock.
     */
    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $purchase = DB::transaction(function () use ($request) {
            $employeeId = auth()->user()->employee->id ?? null;

            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += (float) $item['quantity'] * (float) $item['unit_cost'];
            }
            $subtotal = round($subtotal, 2);
            $tax = round($subtotal * 0.18, 2);
            $total = round($subtotal + $tax, 2);

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'employee_id' => $employeeId,
                'purchase_date' => now(),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'document_number' => $request->document_number,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => round($quantity * $unitCost, 2),
                ]);

                $stockBefore = (float) $product->stock;
                $stockAfter = $stockBefore + $quantity;

                InventoryManagement::create([
                    'product_id' => $product->id,
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $purchase->supplier_id,
                    'employee_id' => $employeeId,
                    'movement_type' => 'inbound',
                    'origin' => 'purchase',
                    'quantity' => $quantity,
                    'reason' => "Compra #{$purchase->id}",
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'movement_date' => now(),
                ]);

                $product->stock = $stockAfter;
                $product->save();
            }

            return $purchase;
        });

        $purchase->load(self::EAGER);

        return response()->json([
            'success' => true,
            'message' => 'Compra registrada. El stock se actualizó.',
            'data' => new SupplierPurchaseResource($purchase),
        ], 201);
    }
}
