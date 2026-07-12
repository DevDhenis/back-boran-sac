<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $person = Person::factory()->create();

        return [
            'person_id' => $person->id,
            'work_schedule' => $this->faker->randomElement([
                'Lunes a viernes 9am-6pm',
                'Turno noche 10pm-6am',
                'Horario flexible',
            ]),
            'salary' => $this->faker->randomFloat(3, 1000, 3000),
            'status' => 'A',
        ];
    }
}
