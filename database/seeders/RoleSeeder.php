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
        // No usamos upsert() porque roles.name no tiene índice UNIQUE, y sin él
        // upsert falla en sqlite y duplica filas en MySQL al re-ejecutar.
        $roles = [
            [
                'name' => 'Administrador General',
                'description' => 'Acceso total a todos los módulos del sistema',
            ],
            [
                'name' => 'Cliente',
                'description' => 'Acceso público a la tienda',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'description' => $role['description'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}
