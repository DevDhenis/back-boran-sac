<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleReturn\StoreSaleReturnRequest;
use App\Http\Resources\SaleReturnResource;
use App\Models\InventoryManagement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SalesItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    private const EAGER = [
        'sale',
        'client.person',
        'reviewedBy.person',
        'items.product',
        'items.salesItem',
    ];

    /**
     * Staff: list every return request (optionally filtered by status).
     */
    public function index(Request $request): JsonResponse
    {
        $query = SaleReturn::with(self::EAGER)->orderByDesc('created_at');

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Devoluciones obtenidas correctamente.',
            'data' => SaleReturnResource::collection($query->get()),
        ]);
    }

    /**
     * Client: list their own return requests.
     */
    public function myReturns(): JsonResponse
    {
        $client = auth()->user()?->client;
        if (! $client) {
            return response()->json(['success' => false, 'message' => 'El usuario no es un cliente válido.'], 403);
        }

        $returns = SaleReturn::with(self::EAGER)
            ->where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Devoluciones obtenidas correctamente.',
            'data' => SaleReturnResource::collection($returns),
        ]);
    }

    /**
     * Client: request a (possibly partial) return for a delivered sale.
     */
    public function store(StoreSaleReturnRequest $request): JsonResponse
    {
        $client = auth()->user()?->client;
        if (! $client) {
            return response()->json(['success' => false, 'message' => 'El usuario no es un cliente válido.'], 403);
        }

        $sale = Sale::with('items')->findOrFail($request->sale_id);

        if ($sale->customer_id !== $client->id) {
            return response()->json(['success' => false, 'message' => 'Esta venta no te pertenece.'], 403);
        }
        if ($sale->status !== 'delivered') {
            return response()->json(['success' => false, 'message' => 'Solo puedes devolver productos de una venta entregada.'], 422);
        }

        $saleItemsById = $sale->items->keyBy('id');
        $requestedItems = collect($request->items);

        // How much of each sale item was already returned (excluding rejected requests).
        $alreadyReturned = SaleReturnItem::whereHas('saleReturn', fn ($q) => $q->where('status', '!=', 'rejected'))
            ->whereIn('sales_item_id', $requestedItems->pluck('sales_item_id'))
            ->selectRaw('sales_item_id, SUM(quantity) as qty')
            ->groupBy('sales_item_id')
            ->pluck('qty', 'sales_item_id');

        foreach ($requestedItems as $reqItem) {
            $salesItem = $saleItemsById->get($reqItem['sales_item_id']);
            if (! $salesItem) {
                return response()->json(['success' => false, 'message' => 'Un producto no pertenece a esta venta.'], 422);
            }
            $available = (float) $salesItem->quantity - (float) ($alreadyReturned[$salesItem->id] ?? 0);
            if ((float) $reqItem['quantity'] > $available) {
                return response()->json([
                    'success' => false,
                    'message' => "No puedes devolver más de {$available} unidad(es) de \"{$salesItem->product->name}\".",
                ], 422);
            }
        }

        $saleReturn = DB::transaction(function () use ($client, $sale, $request, $requestedItems, $saleItemsById) {
            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'client_id' => $client->id,
                'status' => 'requested',
                'reason' => $request->reason,
                'refund_status' => 'pending',
            ]);

            foreach ($requestedItems as $reqItem) {
                $salesItem = $saleItemsById->get($reqItem['sales_item_id']);
                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'sales_item_id' => $salesItem->id,
                    'product_id' => $salesItem->product_id,
                    'quantity' => $reqItem['quantity'],
                ]);
            }

            return $saleReturn;
        });

        $saleReturn->load(self::EAGER);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de devolución enviada. La empresa la revisará.',
            'data' => new SaleReturnResource($saleReturn),
        ], 201);
    }

    /**
     * Staff: approve a return -> restore stock via traceable inbound movements.
     */
    public function approve(SaleReturn $saleReturn): JsonResponse
    {
        if ($saleReturn->status !== 'requested') {
            return response()->json(['success' => false, 'message' => 'Esta devolución ya fue resuelta.'], 422);
        }

        DB::transaction(function () use ($saleReturn) {
            $employeeId = auth()->user()->employee->id ?? null;

            foreach ($saleReturn->items()->get() as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if (! $product) {
                    continue;
                }

                $stockBefore = (float) $product->stock;
                $stockAfter = $stockBefore + (float) $item->quantity;

                InventoryManagement::create([
                    'product_id' => $product->id,
                    'sale_id' => $saleReturn->sale_id,
                    'sale_return_id' => $saleReturn->id,
                    'employee_id' => $employeeId,
                    'movement_type' => 'inbound',
                    'origin' => 'customer_return',
                    'quantity' => (float) $item->quantity,
                    'reason' => "Devolución venta #{$saleReturn->sale_id}",
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'movement_date' => now(),
                ]);

                $product->stock = $stockAfter;
                $product->save();
            }

            $saleReturn->update([
                'status' => 'approved',
                'reviewed_by_employee_id' => $employeeId,
                'resolved_at' => now(),
            ]);
        });

        $saleReturn->load(self::EAGER);

        return response()->json([
            'success' => true,
            'message' => 'Devolución aprobada. El stock fue restaurado.',
            'data' => new SaleReturnResource($saleReturn),
        ]);
    }

    /**
     * Staff: reject a return (no stock change).
     */
    public function reject(Request $request, SaleReturn $saleReturn): JsonResponse
    {
        $request->validate(['review_note' => 'nullable|string|max:500']);

        if ($saleReturn->status !== 'requested') {
            return response()->json(['success' => false, 'message' => 'Esta devolución ya fue resuelta.'], 422);
        }

        $saleReturn->update([
            'status' => 'rejected',
            'reviewed_by_employee_id' => auth()->user()->employee->id ?? null,
            'review_note' => $request->review_note,
            'resolved_at' => now(),
        ]);

        $saleReturn->load(self::EAGER);

        return response()->json([
            'success' => true,
            'message' => 'Devolución rechazada.',
            'data' => new SaleReturnResource($saleReturn),
        ]);
    }

    /**
     * Staff: register the money refund for an approved return (separate step).
     */
    public function refund(SaleReturn $saleReturn): JsonResponse
    {
        if ($saleReturn->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Solo se puede reembolsar una devolución aprobada.'], 422);
        }
        if ($saleReturn->refund_status === 'refunded') {
            return response()->json(['success' => false, 'message' => 'Esta devolución ya fue reembolsada.'], 422);
        }

        $saleReturn->update(['refund_status' => 'refunded']);
        $saleReturn->load(self::EAGER);

        return response()->json([
            'success' => true,
            'message' => 'Reembolso registrado correctamente.',
            'data' => new SaleReturnResource($saleReturn),
        ]);
    }
}
