<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrInsert (idempotente): busca por 'name'; si ya existe no duplica.
        $accesses = [
            ['name' => 'Catálogo',                 'path' => '/catalogo',     'icon' => 'pi pi-shopping-bag'],
            ['name' => 'Historial de compras',     'path' => '/historial',    'icon' => 'pi pi-history'],
            ['name' => 'Acerca de nosotros',       'path' => '/acerca',       'icon' => 'pi pi-info-circle'],
            ['name' => 'Gestión de trabajadores',  'path' => '/trabajadores', 'icon' => 'pi pi-users'],
            ['name' => 'Gestión de inventario',    'path' => '/inventario',   'icon' => 'pi pi-box'],
            ['name' => 'Ventas',                   'path' => '/ventas',       'icon' => 'pi pi-dollar'],
            ['name' => 'Gestión de roles',         'path' => '/roles',        'icon' => 'pi pi-id-card'],
        ];

        foreach ($accesses as $access) {
            DB::table('accesses')->updateOrInsert(
                ['name' => $access['name']],
                [
                    'path' => $access['path'],
                    'icon' => $access['icon'],
                    'access_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
