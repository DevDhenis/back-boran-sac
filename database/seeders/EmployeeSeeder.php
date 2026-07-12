<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Faceta de empleado del admin, para que la cuenta admin quede completa
     * como personal (auth()->user()->employee). Sin factories/Faker: usa
     * DB::table para ser idempotente y compatible con producción (--no-dev).
     */
    public function run(): void
    {
        $adminPerson = DB::table('persons')->where('document_number', '99999999')->first();

        if (! $adminPerson) {
            return;
        }

        DB::table('employees')->updateOrInsert(
            ['person_id' => $adminPerson->id],
            [
                'work_schedule' => 'Lunes a Viernes 8:00-17:00',
                'salary' => 0,
                'status' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
