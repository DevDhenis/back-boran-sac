<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\InventoryManagement;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Builds a COHERENT inventory ledger (kardex) per product: every product starts
 * at 0, receives its opening "Inventario inicial" entry and a few movements whose
 * running balance (stock_after) is consistent and ends exactly at the product's
 * stock. It rebuilds the ledger from scratch, so it is safe to re-run.
 */
class InventoryMovementDemoSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('status', 'A')->get();
        if ($products->isEmpty()) {
            $this->command?->warn('InventoryMovementDemoSeeder: no hay productos; corre DemoDataSeeder primero.');

            return;
        }

        $employeeIds = Employee::pluck('id');

        // Rebuild the whole ledger so balances are coherent.
        InventoryManagement::query()->delete();

        foreach ($products as $product) {
            $target = (float) $product->stock;
            if ($target <= 0) {
                $target = 50;
            }

            $balance = 0.0;
            $daysAgo = 90;
            $emp = fn () => $employeeIds->isNotEmpty() ? $employeeIds->random() : null;

            $post = function (string $type, string $origin, float $qty, string $reason, ?float $absoluteAfter = null)
                use (&$balance, &$daysAgo, $product, $emp) {
                $before = $balance;
                $after = match ($type) {
                    'inbound' => $before + $qty,
                    'outbound' => $before - $qty,
                    'adjustment' => (float) $absoluteAfter,
                };
                $storedQty = $type === 'adjustment' ? abs($after - $before) : $qty;

                InventoryManagement::create([
                    'product_id' => $product->id,
                    'employee_id' => $emp(),
                    'movement_type' => $type,
                    'origin' => $origin,
                    'quantity' => round($storedQty, 2),
                    'reason' => $reason,
                    'stock_before' => round($before, 2),
                    'stock_after' => round($after, 2),
                    'movement_date' => now()->subDays($daysAgo)->setTime(rand(8, 18), rand(0, 59)),
                    'status' => 'active',
                ]);

                $balance = $after;
                $daysAgo = max(0, $daysAgo - rand(6, 14));
            };

            // Opening -> purchase -> sale -> occasional customer return -> count adjustment (to target).
            $post('inbound', 'manual', $target, 'Inventario inicial');
            $post('inbound', 'manual', (float) rand(10, 40), 'Compra a proveedor');

            $out = min($balance - 1, (float) rand(5, 30));
            if ($out > 0) {
                $post('outbound', 'sale', $out, 'Venta');
            }

            if (rand(0, 1) === 1) {
                $post('inbound', 'customer_return', (float) rand(1, 8), 'Devolución de cliente');
            }

            if (abs($balance - $target) > 0.001) {
                $post('adjustment', 'manual', 0, 'Ajuste por conteo físico', $target);
            }

            // The product's stock is the final balance of its ledger.
            $product->stock = round($balance, 2);
            $product->save();
        }

        $this->command?->info('InventoryMovementDemoSeeder: kardex coherente generado para '.$products->count().' productos.');
    }
}
