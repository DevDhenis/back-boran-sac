<?php

namespace Database\Factories;

use App\Models\InventoryManagement;
use App\Models\Product;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryManagementFactory extends Factory
{
    protected $model = InventoryManagement::class;

    public function definition()
    {
        $product = Product::inRandomOrder()->first();
        $stockAntes = $product ? (float) $product->stock : $this->faker->randomFloat(3, 0, 200);

        $tipo = $this->faker->randomElement(['entrada','salida','ajuste']);
        $cantidad = $this->faker->randomFloat(3, 1, 50);

        if ($tipo === 'salida' && $product) {
            $cantidad = min($cantidad, max(0.001, $stockAntes));
            $stockDespues = $stockAntes - $cantidad;
        } elseif ($tipo === 'entrada') {
            $stockDespues = $stockAntes + $cantidad;
        } else { // ajuste
            $stockDespues = $this->faker->randomFloat(3, 0, 500);
            $cantidad = abs($stockDespues - $stockAntes);
        }

        return [
            'product_id' => $product?->id,
            'employee_id' => Employee::inRandomOrder()->first()?->id,
            'tipo_movimiento' => $tipo,
            'cantidad' => $cantidad,
            'motivo' => $this->faker->randomElement(['Compra proveedor','Ajuste inventario','Devolución cliente','Ingreso por producción']),
            'stock_antes' => $stockAntes,
            'stock_despues' => $stockDespues,
            'fecha_movimiento' => $this->faker->dateTimeBetween('-60 days', 'now'),
            'estado_registro' => 'activo',
        ];
    }
}