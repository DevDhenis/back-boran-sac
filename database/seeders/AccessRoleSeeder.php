<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            // Rol Admin -> todos los accesos
            $adminRoleId = DB::table('roles')->where('name', 'Administrador General')->value('id');
            $allAccessIds = DB::table('accesses')->pluck('id');
            $rows = [];

            foreach ($allAccessIds as $accessId) {
                $rows[] = [
                    'role_id' => $adminRoleId,
                    'access_id' => $accessId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Rol Cliente -> solo accesos públicos
            $clientRoleId = DB::table('roles')->where('name', 'Cliente')->value('id');
            $publicAccessNames = [
                'Catálogo',
                'Carrito de compras',
                'Compras',
                'Historial de compras',
                'Acerca de nosotros',
            ];

            $publicAccessIds = DB::table('accesses')
                ->whereIn('name', $publicAccessNames)
                ->pluck('id');

            foreach ($publicAccessIds as $accessId) {
                $rows[] = [
                    'role_id' => $clientRoleId,
                    'access_id' => $accessId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($rows)) {
                DB::table('access_roles')->insertOrIgnore($rows);
            }
        });
    }
}
