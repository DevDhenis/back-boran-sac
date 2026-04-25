<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'sale_id' => null, // se asigna manualmente en el seeder
            'method' => 'tarjeta',
            'amount' => $this->faker->randomFloat(2, 20, 300),
            'payment_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'status' => $this->faker->randomElement(['pendiente', 'confirmado', 'fallido']),
            'card_holder' => $this->faker->name(),
            'card_last4' => str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
            'card_expiration' => '12/30',
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
