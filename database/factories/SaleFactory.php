<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    public function definition()
    {
        return [
            'customer_id' => \App\Models\Client::factory(),
            'employee_id' => \App\Models\Employee::factory(),
            'sale_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'status' => $this->faker->randomElement([
                'pendiente_envio',
                'en_preparacion',
                'en_camino',
                'entregado',
                'cancelado'
            ]),
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
        ];
    }
}
