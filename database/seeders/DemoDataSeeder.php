<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Contenido de demo (bajo pedido): 3 roles de staff con sus accesos,
 * 5 colaboradores completos (person + employee + user) y 10 productos
 * ligados a categorías/unidades existentes (imágenes las carga el usuario).
 *
 * Idempotente: se puede correr varias veces sin duplicar.
 * Uso: php artisan db:seed --class=DemoDataSeeder --force
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $dniId = DB::table('document_types')->where('name', 'DNI')->value('id');

        // ---------------------------------------------------------------
        // 1) 3 roles de staff + sus accesos
        // ---------------------------------------------------------------
        $roles = [
            'Vendedor' => [
                'description' => 'Atiende ventas y consulta el catálogo.',
                'accesses' => ['Catálogo', 'Ventas', 'Historial de compras', 'Acerca de nosotros'],
            ],
            'Almacenero' => [
                'description' => 'Gestiona el inventario y el stock de productos.',
                'accesses' => ['Catálogo', 'Gestión de inventario', 'Acerca de nosotros'],
            ],
            'Cajero' => [
                'description' => 'Registra pagos y cierres de venta.',
                'accesses' => ['Catálogo', 'Ventas', 'Historial de compras', 'Acerca de nosotros'],
            ],
        ];

        $roleIds = [];
        foreach ($roles as $name => $data) {
            DB::table('roles')->updateOrInsert(
                ['name' => $name],
                ['description' => $data['description'], 'created_at' => $now, 'updated_at' => $now]
            );
            $roleId = DB::table('roles')->where('name', $name)->value('id');
            $roleIds[$name] = $roleId;

            $accessIds = DB::table('accesses')->whereIn('name', $data['accesses'])->pluck('id');
            $pivot = $accessIds->map(fn ($accessId) => [
                'role_id' => $roleId,
                'access_id' => $accessId,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            if (! empty($pivot)) {
                DB::table('access_roles')->insertOrIgnore($pivot);
            }
        }

        // ---------------------------------------------------------------
        // 2) 5 colaboradores completos (person + employee + user)
        // ---------------------------------------------------------------
        $collaborators = [
            ['first_name' => 'Carla', 'last_name' => 'Rojas', 'second_last_name' => 'Vega', 'doc' => '40000001', 'username' => 'crojas', 'role' => 'Vendedor', 'salary' => 1500],
            ['first_name' => 'Miguel', 'last_name' => 'Torres', 'second_last_name' => 'Díaz', 'doc' => '40000002', 'username' => 'mtorres', 'role' => 'Vendedor', 'salary' => 1500],
            ['first_name' => 'Lucía', 'last_name' => 'Fernández', 'second_last_name' => 'Soto', 'doc' => '40000003', 'username' => 'lfernandez', 'role' => 'Almacenero', 'salary' => 1600],
            ['first_name' => 'Jorge', 'last_name' => 'Ramírez', 'second_last_name' => 'León', 'doc' => '40000004', 'username' => 'jramirez', 'role' => 'Almacenero', 'salary' => 1600],
            ['first_name' => 'Diana', 'last_name' => 'Salas', 'second_last_name' => 'Curo', 'doc' => '40000005', 'username' => 'dsalas', 'role' => 'Cajero', 'salary' => 1550],
        ];

        foreach ($collaborators as $c) {
            DB::table('persons')->updateOrInsert(
                ['document_number' => $c['doc']],
                [
                    'first_name' => $c['first_name'],
                    'last_name' => $c['last_name'],
                    'second_last_name' => $c['second_last_name'],
                    'address' => 'Av. Ferremax 100',
                    'image' => null,
                    'document_type_id' => $dniId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $personId = DB::table('persons')->where('document_number', $c['doc'])->value('id');

            DB::table('employees')->updateOrInsert(
                ['person_id' => $personId],
                [
                    'work_schedule' => 'Lunes a Sábado 9:00-18:00',
                    'salary' => $c['salary'],
                    'status' => 'A',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('users')->updateOrInsert(
                ['username' => $c['username']],
                [
                    'password' => Hash::make('password123'),
                    'email' => $c['username'].'@ferremax.com',
                    'email_verified_at' => $now,
                    'status' => 'A',
                    'role_id' => $roleIds[$c['role']],
                    'person_id' => $personId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // ---------------------------------------------------------------
        // 3) 10 productos ligados a categorías/unidades existentes (sin imagen)
        // ---------------------------------------------------------------
        $unit = fn ($abbr) => DB::table('units')->where('abbreviation', $abbr)->value('id');
        $cat = fn ($name) => DB::table('product_categories')->where('name', $name)->value('id');

        $products = [
            ['internal_code' => 'FMX-0001', 'name' => 'Alicate universal 8"', 'description' => 'Alicate de acero con mango aislado, para corte y sujeción.', 'stock' => 60, 'minimum_quantity' => 5, 'on_promotion' => false, 'unit_price' => 24.90, 'wholesale_unit_price' => 21.50, 'wholesale_min_quantity' => 10, 'discount' => 0, 'unit' => 'u', 'cat' => 'Herramientas manuales'],
            ['internal_code' => 'FMX-0002', 'name' => 'Taladro percutor 750W', 'description' => 'Taladro percutor con velocidad variable y reversa, mandril 13mm.', 'stock' => 25, 'minimum_quantity' => 3, 'on_promotion' => true, 'unit_price' => 189.90, 'wholesale_unit_price' => 172.00, 'wholesale_min_quantity' => 5, 'discount' => 8, 'unit' => 'u', 'cat' => 'Herramientas eléctricas'],
            ['internal_code' => 'FMX-0003', 'name' => 'Interruptor simple empotrable', 'description' => 'Interruptor de pared 10A, blanco, para instalación empotrada.', 'stock' => 200, 'minimum_quantity' => 20, 'on_promotion' => false, 'unit_price' => 7.50, 'wholesale_unit_price' => 6.30, 'wholesale_min_quantity' => 30, 'discount' => 0, 'unit' => 'u', 'cat' => 'Electricidad'],
            ['internal_code' => 'FMX-0004', 'name' => 'Tubo PVC 1/2" x 3m', 'description' => 'Tubería PVC para agua a presión, media pulgada, tira de 3 metros.', 'stock' => 140, 'minimum_quantity' => 10, 'on_promotion' => false, 'unit_price' => 12.90, 'wholesale_unit_price' => 11.20, 'wholesale_min_quantity' => 20, 'discount' => 0, 'unit' => 'tb', 'cat' => 'Tuberías y accesorios'],
            ['internal_code' => 'FMX-0005', 'name' => 'Brocha de cerda 3"', 'description' => 'Brocha de cerda natural 3 pulgadas, mango de madera.', 'stock' => 90, 'minimum_quantity' => 8, 'on_promotion' => false, 'unit_price' => 9.90, 'wholesale_unit_price' => 8.40, 'wholesale_min_quantity' => 15, 'discount' => 0, 'unit' => 'u', 'cat' => 'Pinturas y acabados'],
            ['internal_code' => 'FMX-0006', 'name' => 'Cemento Portland 42.5kg', 'description' => 'Bolsa de cemento Portland tipo I, 42.5 kg, uso estructural.', 'stock' => 300, 'minimum_quantity' => 25, 'on_promotion' => true, 'unit_price' => 28.50, 'wholesale_unit_price' => 26.00, 'wholesale_min_quantity' => 50, 'discount' => 5, 'unit' => 'u', 'cat' => 'Construcción'],
            ['internal_code' => 'FMX-0007', 'name' => 'Pernos hexagonales 1/2" (paq. 50)', 'description' => 'Paquete de 50 pernos hexagonales galvanizados de media pulgada.', 'stock' => 70, 'minimum_quantity' => 6, 'on_promotion' => false, 'unit_price' => 18.90, 'wholesale_unit_price' => 16.50, 'wholesale_min_quantity' => 12, 'discount' => 0, 'unit' => 'paq', 'cat' => 'Fijaciones'],
            ['internal_code' => 'FMX-0008', 'name' => 'Casco de seguridad', 'description' => 'Casco de seguridad industrial con ajuste de matraca, certificado.', 'stock' => 110, 'minimum_quantity' => 10, 'on_promotion' => false, 'unit_price' => 21.90, 'wholesale_unit_price' => 19.00, 'wholesale_min_quantity' => 20, 'discount' => 0, 'unit' => 'u', 'cat' => 'Seguridad industrial'],
            ['internal_code' => 'FMX-0009', 'name' => 'Foco LED 12W luz fría', 'description' => 'Foco LED de 12W, luz fría, rosca E27, bajo consumo.', 'stock' => 180, 'minimum_quantity' => 15, 'on_promotion' => true, 'unit_price' => 8.90, 'wholesale_unit_price' => 7.50, 'wholesale_min_quantity' => 24, 'discount' => 10, 'unit' => 'u', 'cat' => 'Iluminación'],
            ['internal_code' => 'FMX-0010', 'name' => 'Disco de corte metal 4.5"', 'description' => 'Disco de corte para metal 4.5 pulgadas, para amoladora.', 'stock' => 160, 'minimum_quantity' => 12, 'on_promotion' => false, 'unit_price' => 3.50, 'wholesale_unit_price' => 2.90, 'wholesale_min_quantity' => 40, 'discount' => 0, 'unit' => 'u', 'cat' => 'Abrasivos'],
        ];

        foreach ($products as $p) {
            DB::table('products')->updateOrInsert(
                ['internal_code' => $p['internal_code']],
                [
                    'name' => $p['name'],
                    'description' => $p['description'],
                    'stock' => $p['stock'],
                    'minimum_quantity' => $p['minimum_quantity'],
                    'on_promotion' => $p['on_promotion'],
                    'unit_price' => $p['unit_price'],
                    'wholesale_unit_price' => $p['wholesale_unit_price'],
                    'wholesale_min_quantity' => $p['wholesale_min_quantity'],
                    'discount' => $p['discount'],
                    'final_price' => $p['unit_price'],
                    'image' => null,
                    'unit_id' => $unit($p['unit']),
                    'product_category_id' => $cat($p['cat']),
                    'status' => 'A',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // Build a coherent inventory ledger (kardex) for the products just seeded.
        $this->call(InventoryMovementDemoSeeder::class);
    }
}
