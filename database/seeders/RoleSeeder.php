<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->upsert([
            [
                'nombre'      => 'Administrador General',
                'descripcion' => 'Acceso total a todos los módulos del sistema',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'nombre'      => 'Cliente',
                'descripcion' => 'Acceso público a la tienda',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
        ], ['nombre'], ['descripcion', 'updated_at']);
    }
}
