<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\InventoryManagement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with([
            'customer.person',
            'employee.person',
            'items.product',
            'payments',
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lista de ventas obtenida correctamente.',
            'data' => SaleResource::collection($sales),
        ]);
    }

    public function show(Sale $sale)
    {
        $saleDetail = $sale->load([
            'customer.person',
            'employee.person',
            'items.product',
            'payments',
            'statusHistories.changedByEmployee.person',
            'statusHistories.changedByClient.person',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Venta obtenida correctamente.',
            'data' => new SaleResource($saleDetail),
        ]);
    }

    public function changeStatus(Request $request, Sale $sale)
    {
        $request->validate([
            'new_status' => 'required|in:pending_shipment,in_preparation,in_transit,delivered,cancelled',
            'reason' => 'nullable|string',
        ]);

        $previous = $sale->status;
        $newStatus = $request->new_status;

        return DB::transaction(function () use ($request, $sale, $previous, $newStatus) {
            $sale->update([
                'status' => $newStatus,
            ]);

            SaleStatusHistory::create([
                'sale_id' => $sale->id,
                'previous_status' => $previous,
                'new_status' => $newStatus,
                'changed_by_employee_id' => auth()->user()->employee->id ?? null,
                'changed_by_client_id' => null,
                'reason' => $request->reason,
            ]);

            // Cancelling a not-yet-delivered sale returns its stock to inventory as a
            // traceable movement. A delivered sale isn't cancelled (that's a Return).
            // Idempotent: skip if the restoration was already posted.
            $cancellableFrom = ['pending_shipment', 'in_preparation', 'in_transit'];
            $alreadyRestored = InventoryManagement::where('sale_id', $sale->id)
                ->where('origin', 'sale_cancellation')
                ->exists();

            if ($newStatus === 'cancelled' && in_array($previous, $cancellableFrom, true) && ! $alreadyRestored) {
                $employeeId = auth()->user()->employee->id ?? null;

                foreach ($sale->items()->get() as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if (! $product) {
                        continue;
                    }

                    $stockBefore = (float) $product->stock;
                    $stockAfter = $stockBefore + (float) $item->quantity;

                    InventoryManagement::create([
                        'product_id' => $product->id,
                        'sale_id' => $sale->id,
                        'employee_id' => $employeeId,
                        'movement_type' => 'inbound',
                        'origin' => 'sale_cancellation',
                        'quantity' => (float) $item->quantity,
                        'reason' => "Cancelación venta #{$sale->id}",
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'movement_date' => now(),
                    ]);

                    $product->stock = $stockAfter;
                    $product->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado de la venta actualizado correctamente.',
            ]);
        });
    }

    public function mySales()
    {
        $user = auth()->user();

        if (! $user || ! $user->client) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es un cliente válido.',
            ], 403);
        }

        $sales = Sale::where('customer_id', $user->client->id)
            ->with([
                'customer.person',
                'employee.person',
                'items.product.unit',
                'items.product.category',
                'payments',
                'statusHistories',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Historial de compras obtenido correctamente.',
            'data' => SaleResource::collection($sales),
        ]);
    }
}
