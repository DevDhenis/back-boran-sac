<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition()
    {
        return [
            'person_id' => \App\Models\Person::factory(),
            'total_purchases' => $this->faker->numberBetween(0, 50),
            'accepted_purchases' => $this->faker->numberBetween(0, 50),
            'rejected_purchases' => $this->faker->numberBetween(0, 10),
            'returned_purchases' => $this->faker->numberBetween(0, 5),
        ];
    }
}
