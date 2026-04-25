<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseListResource;
use App\Http\Resources\PurchaseResource;
use App\Models\Sale;
use Illuminate\Http\Request;

class PurchaseHistoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);

        $query = Sale::query()
            ->with([
                'customer.person:id,nombres,apellido_paterno,apellido_materno',
                'payments'
            ])
            ->when($request->date_from, fn($q) => $q->whereDate('sale_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('sale_date', '<=', $request->date_to))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when(
                $request->payment_method,
                fn($q) =>
                $q->whereHas('payments', fn($p) => $p->where('method', $request->payment_method))
            );

        $results = $query->orderBy('sale_date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Listado de compras',
            'data' => PurchaseListResource::collection($results),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ]
        ]);
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'items.product',
            'payments',
            'statusHistories',
            'customer.person',
            'employee.person'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detalle de compra',
            'data' => new PurchaseResource($sale)
        ]);
    }

    public function history(Sale $sale)
    {
        return response()->json([
            'success' => true,
            'message' => 'Historial de estados',
            'data' => $sale->statusHistories
        ]);
    }
}
