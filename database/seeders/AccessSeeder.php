<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('accesses')->insert([
            [
                'nombre'     => 'Catálogo',
                'path'       => '/catalogo',
                'icon'       => 'pi pi-shopping-bag',
                'access_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Historial de compras',
                'path'       => '/historial',
                'icon'       => 'pi pi-history',
                'access_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Acerca de nosotros',
                'path'       => '/acerca',
                'icon'       => 'pi pi-info-circle',
                'access_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Gestión de trabajadores',
                'path'       => '/trabajadores',
                'icon'       => 'pi pi-users',
                'access_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Gestión de inventario',
                'path'       => '/inventario',
                'icon'       => 'pi pi-box',
                'access_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Ventas',
                'path'       => '/ventas',
                'icon'       => 'pi pi-dollar',
                'access_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Gestión de roles',
                'path'       => '/roles',
                'icon'       => 'pi pi-id-card',
                'access_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
