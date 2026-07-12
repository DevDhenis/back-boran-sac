<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Models\SaleStatusHistory;
use Illuminate\Http\Request;

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

        $sale->update([
            'status' => $request->new_status,
        ]);

        SaleStatusHistory::create([
            'sale_id' => $sale->id,
            'previous_status' => $previous,
            'new_status' => $request->new_status,
            'changed_by_employee_id' => auth()->user()->employee->id ?? null,
            'changed_by_client_id' => null,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de la venta actualizado correctamente.',
        ]);
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
