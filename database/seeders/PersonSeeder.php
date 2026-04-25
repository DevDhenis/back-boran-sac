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
            ['numero_documento' => '99999999'],
            [
                'nombres' => 'Administrador',
                'apellido_paterno' => 'General',
                'apellido_materno' => null,
                'direccion' => 'Oficina principal',
                'imagen' => $avatarPath,
                'document_type_id' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 👤 Persona cliente
        DB::table('persons')->updateOrInsert(
            ['numero_documento' => '88888888'],
            [
                'nombres' => 'Cliente',
                'apellido_paterno' => 'Demo',
                'apellido_materno' => 'Usuario',
                'direccion' => 'Av. Prueba 123',
                'imagen' => $avatarPath,
                'document_type_id' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 👤 Persona cliente 2
        DB::table('persons')->updateOrInsert(
            ['numero_documento' => '77777777'],
            [
                'nombres' => 'Cliente',
                'apellido_paterno' => 'Demo2',
                'apellido_materno' => 'Usuario2',
                'direccion' => 'Av. Secundaria 456',
                'imagen' => $avatarPath,
                'document_type_id' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
