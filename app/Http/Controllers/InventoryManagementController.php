<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestmentManagement\StoreInventoryRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Models\InventoryManagement;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryManagementController extends Controller
{
    public function index(): JsonResponse
    {
        $query = InventoryManagement::with(['product', 'employee.person', 'sale', 'supplier'])
            ->orderByDesc('movement_date')
            ->orderByDesc('id');

        if (request('product_id')) {
            $query->where('product_id', request('product_id'));
        }
        if (request('movement_type')) {
            $query->where('movement_type', request('movement_type'));
        }
        if (request('origin')) {
            $query->where('origin', request('origin'));
        }
        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('from')) {
            $query->where('movement_date', '>=', request('from'));
        }
        if (request('to')) {
            $query->where('movement_date', '<=', request('to'));
        }

        // Client-side table filters/paginates; cap the payload to stay light.
        $movements = $query->limit(300)->get();

        return response()->json([
            'success' => true,
            'message' => 'Movimientos obtenidos correctamente.',
            'data' => InventoryMovementResource::collection($movements),
        ]);
    }

    public function show(InventoryManagement $inventoryManagement): JsonResponse
    {
        $inventoryManagement->load(['product', 'employee.person', 'sale']);

        return response()->json([
            'success' => true,
            'message' => 'Movimiento obtenido correctamente.',
            'data' => new InventoryMovementResource($inventoryManagement),
        ]);
    }

    public function store(StoreInventoryRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);

            $stockBefore = (float) $product->stock;
            $quantity = (float) $request->quantity;
            $type = $request->movement_type;

            if ($type === 'outbound' && $quantity > $stockBefore) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente para la salida.',
                ], 422);
            }

            // inbound and return add stock; outbound subtracts; adjustment sets an absolute target.
            $stockAfter = match ($type) {
                'inbound', 'return' => $stockBefore + $quantity,
                'outbound' => $stockBefore - $quantity,
                'adjustment' => $request->filled('stock_after') ? (float) $request->stock_after : $stockBefore,
            };

            // Attribute the movement to the authenticated user's employee when not provided.
            $employeeId = $request->employee_id ?? auth()->user()?->employee?->id;

            $movement = InventoryManagement::create([
                'product_id' => $product->id,
                'employee_id' => $employeeId,
                'movement_type' => $type,
                'origin' => 'manual', // movements registered here are always manual
                'quantity' => $quantity,
                'reason' => $request->reason,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                // Set explicitly (app tz) so the response isn't null and the value is
                // consistent with how it's read back, instead of MySQL's useCurrent.
                'movement_date' => now(),
            ]);

            $product->stock = $stockAfter;
            $product->save();

            $movement->load(['product', 'employee.person', 'sale']);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento registrado correctamente.',
                'data' => new InventoryMovementResource($movement),
            ], 201);
        });
    }

    public function destroy(InventoryManagement $inventoryManagement): JsonResponse
    {
        return DB::transaction(function () use ($inventoryManagement) {
            if ($inventoryManagement->status === 'voided') {
                return response()->json([
                    'success' => false,
                    'message' => 'El movimiento ya está anulado.',
                ], 422);
            }

            // Append-only ledger: only the product's latest active movement can be voided,
            // otherwise the running balance of later movements would become inconsistent.
            $latest = InventoryManagement::where('product_id', $inventoryManagement->product_id)
                ->where('status', 'active')
                ->orderByDesc('movement_date')
                ->orderByDesc('id')
                ->first();

            if (! $latest || $latest->id !== $inventoryManagement->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes anular el último movimiento del producto. Para corregir uno anterior, registra un ajuste.',
                ], 422);
            }

            $product = Product::lockForUpdate()->find($inventoryManagement->product_id);
            if ($product) {
                $product->stock = $inventoryManagement->stock_before;
                $product->save();
            }

            $inventoryManagement->update(['status' => 'voided']);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento anulado correctamente.',
            ]);
        });
    }

    /**
     * Product traceability: chronological ledger (oldest first) where stock_after is
     * the running balance after each movement.
     */
    public function kardex(Product $product): JsonResponse
    {
        $movements = InventoryManagement::with(['employee.person', 'sale', 'supplier'])
            ->where('product_id', $product->id)
            ->where('status', 'active')
            ->orderBy('movement_date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Kardex obtenido correctamente.',
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'internal_code' => $product->internal_code,
                    'name' => $product->name,
                    'stock' => (float) $product->stock,
                    'minimum_quantity' => (float) $product->minimum_quantity,
                    'unit' => $product->unit?->abbreviation ?? $product->unit?->name ?? '',
                ],
                'movements' => InventoryMovementResource::collection($movements),
            ],
        ]);
    }

    /**
     * Data for the on-demand traceability document (never stored): per-product kardex
     * + summary, filtered by date range and/or product.
     */
    public function report(Request $request): JsonResponse
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $productId = $request->query('product_id');

        $query = InventoryManagement::with(['employee.person', 'supplier'])->where('status', 'active');

        if ($from) {
            $query->whereDate('movement_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('movement_date', '<=', $to);
        }
        if ($productId) {
            $query->where('product_id', $productId);
        }

        $movements = $query->orderBy('product_id')->orderBy('movement_date')->orderBy('id')->get();

        $products = Product::with(['unit', 'category'])
            ->whereIn('id', $movements->pluck('product_id')->unique())
            ->get()->keyBy('id');

        $groups = $movements->groupBy('product_id')->map(function ($movs, $pid) use ($products) {
            $p = $products->get($pid);

            return [
                'product' => $p ? [
                    'internal_code' => $p->internal_code,
                    'name' => $p->name,
                    'category' => $p->category?->name,
                    'unit' => $p->unit?->abbreviation ?? $p->unit?->name ?? '',
                    'stock' => (float) $p->stock,
                    'minimum_quantity' => (float) $p->minimum_quantity,
                ] : null,
                'movements' => $movs->map(function ($m) {
                    $person = $m->employee?->person;

                    return [
                        'movement_date' => $m->movement_date,
                        'movement_type' => $m->movement_type,
                        'origin' => $m->origin,
                        'quantity' => (float) $m->quantity,
                        'stock_before' => (float) $m->stock_before,
                        'stock_after' => (float) $m->stock_after,
                        'reason' => $m->reason,
                        'sale_id' => $m->sale_id,
                        'supplier' => $m->supplier?->name,
                        'employee' => $person
                            ? trim("{$person->first_name} {$person->last_name}")
                            : '—',
                    ];
                })->values(),
            ];
        })->values();

        $byType = [];
        foreach (['inbound', 'outbound', 'adjustment'] as $t) {
            $subset = $movements->where('movement_type', $t);
            $byType[$t] = ['count' => $subset->count(), 'quantity' => round((float) $subset->sum('quantity'), 2)];
        }

        $byOrigin = [];
        foreach (['manual', 'sale', 'sale_cancellation', 'customer_return', 'purchase', 'supplier_return'] as $o) {
            $byOrigin[$o] = $movements->where('origin', $o)->count();
        }

        return response()->json([
            'success' => true,
            'message' => 'Reporte de trazabilidad generado.',
            'data' => [
                'generated_at' => now(),
                'range' => ['from' => $from, 'to' => $to],
                'total_movements' => $movements->count(),
                'by_type' => $byType,
                'by_origin' => $byOrigin,
                'groups' => $groups,
            ],
        ]);
    }
}
