<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrInsert (idempotente): busca por 'nombre'; si ya existe no duplica.
        $accesses = [
            ['nombre' => 'Catálogo',                 'path' => '/catalogo',     'icon' => 'pi pi-shopping-bag'],
            ['nombre' => 'Historial de compras',     'path' => '/historial',    'icon' => 'pi pi-history'],
            ['nombre' => 'Acerca de nosotros',       'path' => '/acerca',       'icon' => 'pi pi-info-circle'],
            ['nombre' => 'Gestión de trabajadores',  'path' => '/trabajadores', 'icon' => 'pi pi-users'],
            ['nombre' => 'Gestión de inventario',    'path' => '/inventario',   'icon' => 'pi pi-box'],
            ['nombre' => 'Ventas',                   'path' => '/ventas',       'icon' => 'pi pi-dollar'],
            ['nombre' => 'Gestión de roles',         'path' => '/roles',        'icon' => 'pi pi-id-card'],
        ];

        foreach ($accesses as $access) {
            DB::table('accesses')->updateOrInsert(
                ['nombre' => $access['nombre']],
                [
                    'path'       => $access['path'],
                    'icon'       => $access['icon'],
                    'access_id'  => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
