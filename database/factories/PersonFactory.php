<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'second_last_name' => $this->faker->lastName,
            'address' => $this->faker->address,
            'image' => null,
            'document_number' => $this->faker->unique()->numerify('########'),
            'document_type_id' => 1, // Asegúrate de tener un tipo de documento con ID 1
        ];
    }
}
