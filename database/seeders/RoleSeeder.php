<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrInsert (idempotente y compatible con MySQL/MariaDB y sqlite).
        // No usamos upsert() porque roles.nombre no tiene índice UNIQUE, y sin él
        // upsert falla en sqlite y duplica filas en MySQL al re-ejecutar.
        $roles = [
            [
                'nombre'      => 'Administrador General',
                'descripcion' => 'Acceso total a todos los módulos del sistema',
            ],
            [
                'nombre'      => 'Cliente',
                'descripcion' => 'Acceso público a la tienda',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['nombre' => $role['nombre']],
                [
                    'descripcion' => $role['descripcion'],
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now(),
                ]
            );
        }
    }
}
