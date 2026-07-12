<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\InventoryManagement;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryManagementFactory extends Factory
{
    protected $model = InventoryManagement::class;

    public function definition()
    {
        $product = Product::inRandomOrder()->first();
        $stockBefore = $product ? (float) $product->stock : $this->faker->randomFloat(3, 0, 200);

        $type = $this->faker->randomElement(['inbound', 'outbound', 'adjustment']);
        $quantity = $this->faker->randomFloat(3, 1, 50);

        if ($type === 'outbound' && $product) {
            $quantity = min($quantity, max(0.001, $stockBefore));
            $stockAfter = $stockBefore - $quantity;
        } elseif ($type === 'inbound') {
            $stockAfter = $stockBefore + $quantity;
        } else { // adjustment
            $stockAfter = $this->faker->randomFloat(3, 0, 500);
            $quantity = abs($stockAfter - $stockBefore);
        }

        return [
            'product_id' => $product?->id,
            'employee_id' => Employee::inRandomOrder()->first()?->id,
            'movement_type' => $type,
            'quantity' => $quantity,
            'reason' => $this->faker->randomElement(['Compra proveedor', 'Ajuste inventario', 'Devolución cliente', 'Ingreso por producción']),
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'movement_date' => $this->faker->dateTimeBetween('-60 days', 'now'),
            'status' => 'active',
        ];
    }
}
