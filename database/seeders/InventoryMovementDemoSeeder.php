<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\InventoryManagement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Builds a COHERENT inventory ledger per product WITHOUT manual "Entrada": stock enters
 * through real Compras (origin=purchase, linked to a supplier); corrections use Ajuste;
 * plus a sale and an occasional customer return. The running balance ends at the
 * product's stock. Rebuilds from scratch, so it is safe to re-run.
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
        $supplierIds = Supplier::pluck('id');

        // Rebuild the ledger and its demo purchases.
        InventoryManagement::query()->delete();
        Purchase::query()->delete();

        foreach ($products as $product) {
            $target = (float) $product->stock;
            if ($target <= 0) {
                $target = 50;
            }

            $balance = 0.0;
            $daysAgo = 90;
            $emp = fn () => $employeeIds->isNotEmpty() ? $employeeIds->random() : null;

            // Non-purchase movement helper (outbound / adjustment / non-manual inbound).
            $post = function (string $type, string $origin, float $qty, string $reason, ?float $absoluteAfter = null, array $links = [])
                use (&$balance, &$daysAgo, $product, $emp) {
                $before = $balance;
                $after = match ($type) {
                    'inbound' => $before + $qty,
                    'outbound' => $before - $qty,
                    'adjustment' => (float) $absoluteAfter,
                };
                $storedQty = $type === 'adjustment' ? abs($after - $before) : $qty;

                InventoryManagement::create(array_merge([
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
                ], $links));

                $balance = $after;
                $daysAgo = max(0, $daysAgo - rand(6, 14));
            };

            // 1) Compra a un proveedor (real): el stock nace aquí.
            if ($supplierIds->isNotEmpty()) {
                $supplierId = $supplierIds->random();
                $buyQty = $target + rand(5, 25);
                $unitCost = round(((float) $product->unit_price) * 0.6, 2);
                $sub = round($buyQty * $unitCost, 2);
                $tax = round($sub * 0.18, 2);

                $purchase = Purchase::create([
                    'supplier_id' => $supplierId,
                    'employee_id' => $emp(),
                    'purchase_date' => now()->subDays($daysAgo)->setTime(rand(8, 18), rand(0, 59)),
                    'subtotal' => $sub,
                    'tax' => $tax,
                    'total' => round($sub + $tax, 2),
                    'document_number' => 'F-'.rand(1000, 9999),
                ]);
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $buyQty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $sub,
                ]);

                $post('inbound', 'purchase', $buyQty, "Compra #{$purchase->id}", null, [
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $supplierId,
                ]);
            } else {
                // Fallback if no suppliers: opening count.
                $post('adjustment', 'manual', 0, 'Inventario inicial', $target + 10);
            }

            // 2) Venta.
            $out = min($balance - 1, (float) rand(5, 30));
            if ($out > 0) {
                $post('outbound', 'sale', $out, 'Venta');
            }

            // 3) Devolución de cliente ocasional.
            if (rand(0, 1) === 1) {
                $post('inbound', 'customer_return', (float) rand(1, 8), 'Devolución de cliente');
            }

            // 4) Ajuste por conteo físico -> deja el saldo en el stock del producto.
            if (abs($balance - $target) > 0.001) {
                $post('adjustment', 'manual', 0, 'Ajuste por conteo físico', $target);
            }

            $product->stock = round($balance, 2);
            $product->save();
        }

        $this->command?->info('InventoryMovementDemoSeeder: kardex coherente (compras + ventas + ajustes) para '.$products->count().' productos.');
    }
}
