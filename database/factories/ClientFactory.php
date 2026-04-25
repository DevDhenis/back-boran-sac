<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition()
    {
        return [
            'person_id' => \App\Models\Person::factory(),
            'cantidad_compras' => $this->faker->numberBetween(0, 50),
            'cantidad_compras_aceptadas' => $this->faker->numberBetween(0, 50),
            'cantidad_compras_rechazadas' => $this->faker->numberBetween(0, 10),
            'cantidad_compras_devueltas' => $this->faker->numberBetween(0, 5),
        ];
    }
}