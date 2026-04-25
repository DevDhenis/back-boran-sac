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
            'nombres' => $this->faker->firstName,
            'apellido_paterno' => $this->faker->lastName,
            'apellido_materno' => $this->faker->lastName,
            'direccion' => $this->faker->address,
            'imagen' => null,
            'numero_documento' => $this->faker->unique()->numerify('########'),
            'document_type_id' => 1, // Asegúrate de tener un tipo de documento con ID 1
        ];
    }
}