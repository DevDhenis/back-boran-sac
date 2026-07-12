<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        $avatarPath = 'images/user-default.jpg';

        // 👤 Persona administrador
        DB::table('persons')->updateOrInsert(
            ['document_number' => '99999999'],
            [
                'first_name' => 'Administrador',
                'last_name' => 'General',
                'second_last_name' => null,
                'address' => 'Oficina principal',
                'image' => $avatarPath,
                'document_type_id' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 👤 Persona cliente
        DB::table('persons')->updateOrInsert(
            ['document_number' => '88888888'],
            [
                'first_name' => 'Cliente',
                'last_name' => 'Demo',
                'second_last_name' => 'Usuario',
                'address' => 'Av. Prueba 123',
                'image' => $avatarPath,
                'document_type_id' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
