<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesItemFactory extends Factory
{
    public function definition()
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->randomFloat(2, 5, 200);
        $discount = $this->faker->randomFloat(2, 0, 10);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'discount' => $discount,
            'subtotal' => ($price - $discount) * $quantity,
        ];
    }
}
