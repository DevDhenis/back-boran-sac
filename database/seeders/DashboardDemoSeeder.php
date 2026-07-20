<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalesItem;
use Illuminate\Database\Seeder;

/**
 * Local, non-destructive demo data so the dashboard has something to show.
 * Idempotent by count: if enough sales already exist it does nothing, so it
 * is safe to re-run and never duplicates.
 */
class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Sale::count() >= 40) {
            $this->command?->warn('DashboardDemoSeeder: ya hay ventas suficientes; se omite.');

            return;
        }

        $products = Product::where('status', 'A')->get();
        if ($products->isEmpty()) {
            $this->command?->warn('DashboardDemoSeeder: no hay productos; corre ProductSeeder/DemoDataSeeder primero.');

            return;
        }

        $clients = Client::all();
        if ($clients->isEmpty()) {
            $clients = Client::factory()->count(5)->create();
        }

        $employees = Employee::all();
        if ($employees->isEmpty()) {
            $employees = Employee::factory()->count(3)->create();
        }

        // Weighted statuses: mostly delivered, a few cancelled.
        $statuses = array_merge(
            array_fill(0, 6, 'delivered'),
            array_fill(0, 2, 'in_transit'),
            array_fill(0, 2, 'in_preparation'),
            array_fill(0, 1, 'pending_shipment'),
            array_fill(0, 1, 'cancelled'),
        );

        for ($i = 0; $i < 55; $i++) {
            $saleDate = now()->subDays(rand(0, 89))->setTime(rand(8, 19), rand(0, 59));
            $status = $statuses[array_rand($statuses)];

            $sale = Sale::create([
                'customer_id' => $clients->random()->id,
                'employee_id' => $employees->random()->id,
                'sale_date' => $saleDate,
                'status' => $status,
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'shipping_address' => 'Av. Ejemplo 123, Lima',
            ]);

            $lineCount = rand(1, 4);
            $subtotal = 0;
            foreach ($products->random(min($lineCount, $products->count())) as $product) {
                $quantity = rand(1, 5);
                $price = (float) $product->unit_price;
                $discount = (float) $product->discount;
                $lineSubtotal = round(($price - ($price * $discount / 100)) * $quantity, 2);
                $subtotal += $lineSubtotal;

                SalesItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => $discount,
                    'subtotal' => $lineSubtotal,
                ]);
            }

            $tax = round($subtotal * 0.18, 2);
            $total = round($subtotal + $tax, 2);
            $sale->update(['subtotal' => $subtotal, 'tax' => $tax, 'total' => $total]);

            // Payments table only supports method 'card' and status confirmed/failed.
            Payment::create([
                'sale_id' => $sale->id,
                'method' => 'card',
                'amount' => $total,
                'payment_date' => $saleDate,
                'status' => $status === 'cancelled' ? 'failed' : 'confirmed',
                'card_holder' => 'Cliente Demo',
                'card_last4' => str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT),
                'card_expiration' => '12/30',
                'phone' => '999888777',
            ]);
        }

        // Inventory movements are owned by InventoryMovementDemoSeeder (coherent kardex).
        $this->command?->info('DashboardDemoSeeder: ventas y pagos demo creados.');
    }
}
