<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\DashboardRequest;
use App\Models\Client;
use App\Models\Employee;
use App\Models\InventoryManagement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\SalesItem;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    private const SALE_STATUSES = [
        'pending_shipment',
        'in_preparation',
        'in_transit',
        'delivered',
        'cancelled',
    ];

    // The payments table only supports these statuses (see payments migration).
    private const PAYMENT_STATUSES = ['confirmed', 'failed'];

    private const MOVEMENT_TYPES = ['inbound', 'outbound', 'adjustment'];

    public function index(DashboardRequest $request): JsonResponse
    {
        $days = $request->days();
        $end = now();
        $start = now()->subDays($days);
        $prevStart = now()->subDays($days * 2);

        // Valid sale = anything not cancelled.
        $currentSales = fn () => Sale::where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$start, $end]);
        $previousSales = fn () => Sale::where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$prevStart, $start]);

        $revenue = (float) $currentSales()->sum('total');
        $orders = (int) $currentSales()->count();
        $prevRevenue = (float) $previousSales()->sum('total');
        $prevOrders = (int) $previousSales()->count();

        $avgTicket = $orders > 0 ? $revenue / $orders : 0.0;
        $prevAvgTicket = $prevOrders > 0 ? $prevRevenue / $prevOrders : 0.0;

        $lowStock = Product::whereColumn('stock', '<=', 'minimum_quantity')
            ->where('status', 'A')
            ->with('unit')
            ->orderBy('stock')
            ->get();

        $data = [
            'range' => $request->query('range', '30d'),
            'kpis' => [
                'revenue' => ['value' => round($revenue, 2), 'delta_pct' => $this->delta($revenue, $prevRevenue)],
                'orders' => ['value' => $orders, 'delta_pct' => $this->delta($orders, $prevOrders)],
                'avg_ticket' => ['value' => round($avgTicket, 2), 'delta_pct' => $this->delta($avgTicket, $prevAvgTicket)],
                'low_stock_count' => ['value' => $lowStock->count()],
            ],
            'revenue_trend' => $this->revenueTrend($start, $end, $days),
            'orders_by_status' => $this->ordersByStatus($start, $end),
            'sales_by_category' => $this->salesByCategory($start, $end),
            'top_products' => $this->topProducts($start, $end),
            'payments_by_status' => $this->paymentsByStatus($start, $end),
            'inventory_movements' => $this->inventoryMovements($start, $end),
            'low_stock' => $lowStock->take(8)->map(fn ($p) => [
                'name' => $p->name,
                'stock' => (float) $p->stock,
                'minimum_quantity' => (float) $p->minimum_quantity,
                'unit' => $p->unit?->abbreviation ?? $p->unit?->name ?? '',
            ])->values(),
            'recent_sales' => $this->recentSales(),
            'counts' => [
                'products' => Product::where('status', 'A')->count(),
                'employees' => Employee::count(),
                'clients' => Client::count(),
                'categories' => ProductCategory::count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Métricas del panel obtenidas correctamente.',
            'data' => $data,
        ]);
    }

    /**
     * Percentage change with guards: never divides by zero, never returns NaN/Infinity.
     */
    private function delta(float $current, float $previous): float
    {
        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100.0 : 0.0;
    }

    /**
     * Continuous daily series (gaps filled with 0) so the chart has no holes.
     */
    private function revenueTrend($start, $end, int $days): array
    {
        $totals = Sale::where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$start, $end])
            ->selectRaw('DATE(sale_date) as d, SUM(total) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $series = [];
        $cursor = $start->copy()->startOfDay();
        for ($i = 0; $i <= $days; $i++) {
            $key = $cursor->format('Y-m-d');
            $series[] = ['date' => $key, 'total' => round((float) ($totals[$key] ?? 0), 2)];
            $cursor->addDay();
        }

        return $series;
    }

    private function ordersByStatus($start, $end): array
    {
        $counts = Sale::whereBetween('sale_date', [$start, $end])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return collect(self::SALE_STATUSES)
            ->map(fn ($status) => ['status' => $status, 'count' => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    private function salesByCategory($start, $end): array
    {
        return SalesItem::query()
            ->join('sales', 'sales.id', '=', 'sales_items.sale_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->where('sales.status', '!=', 'cancelled')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->groupBy('product_categories.id', 'product_categories.name')
            ->selectRaw('product_categories.name as category, SUM(sales_items.subtotal) as revenue')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($r) => ['category' => $r->category, 'revenue' => round((float) $r->revenue, 2)])
            ->all();
    }

    private function topProducts($start, $end): array
    {
        return SalesItem::query()
            ->join('sales', 'sales.id', '=', 'sales_items.sale_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->where('sales.status', '!=', 'cancelled')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name as product, SUM(sales_items.quantity) as quantity, SUM(sales_items.subtotal) as revenue')
            ->orderByDesc('quantity')
            ->limit(6)
            ->get()
            ->map(fn ($r) => [
                'product' => $r->product,
                'quantity' => (float) $r->quantity,
                'revenue' => round((float) $r->revenue, 2),
            ])
            ->all();
    }

    private function paymentsByStatus($start, $end): array
    {
        $rows = Payment::whereBetween('payment_date', [$start, $end])
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return collect(self::PAYMENT_STATUSES)
            ->map(fn ($status) => [
                'status' => $status,
                'count' => (int) ($rows[$status]->count ?? 0),
                'total' => round((float) ($rows[$status]->total ?? 0), 2),
            ])
            ->all();
    }

    private function inventoryMovements($start, $end): array
    {
        $counts = InventoryManagement::where('status', 'active')
            ->whereBetween('movement_date', [$start, $end])
            ->selectRaw('movement_type, COUNT(*) as count')
            ->groupBy('movement_type')
            ->pluck('count', 'movement_type');

        return collect(self::MOVEMENT_TYPES)
            ->map(fn ($type) => ['movement_type' => $type, 'count' => (int) ($counts[$type] ?? 0)])
            ->all();
    }

    private function recentSales(): array
    {
        return Sale::with('customer.person')
            ->orderByDesc('sale_date')
            ->limit(8)
            ->get()
            ->map(function ($sale) {
                $person = $sale->customer?->person;
                $customer = $person
                    ? trim("{$person->first_name} {$person->last_name}")
                    : '—';

                return [
                    'id' => $sale->id,
                    'sale_date' => $sale->sale_date,
                    'customer' => $customer !== '' ? $customer : '—',
                    'total' => round((float) $sale->total, 2),
                    'status' => $sale->status,
                ];
            })
            ->all();
    }
}
