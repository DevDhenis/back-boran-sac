<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $suppliers = [
            ['name' => 'Distribuidora Andina S.A.C.', 'ruc' => '20100112233', 'contact_name' => 'Rosa Mendoza', 'phone' => '014567890', 'email' => 'ventas@andina.pe', 'address' => 'Av. Argentina 1200, Lima'],
            ['name' => 'Ferretería Mayorista del Sur', 'ruc' => '20455667788', 'contact_name' => 'Luis Paredes', 'phone' => '959123456', 'email' => 'compras@masur.pe', 'address' => 'Jr. Cusco 340, Arequipa'],
            ['name' => 'Importaciones Herramax', 'ruc' => '20566778899', 'contact_name' => 'Karina Ruiz', 'phone' => '987654321', 'email' => 'contacto@herramax.pe', 'address' => 'Av. Industrial 890, Callao'],
            ['name' => 'Aceros y Fijaciones Perú', 'ruc' => '20677889900', 'contact_name' => 'Diego Flores', 'phone' => '013344556', 'email' => 'pedidos@acerosperu.pe', 'address' => 'Av. Colonial 2100, Lima'],
        ];

        foreach ($suppliers as $s) {
            DB::table('suppliers')->updateOrInsert(
                ['ruc' => $s['ruc']],
                array_merge($s, ['status' => 'A', 'created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
