<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Herramientas manuales',
                'description' => 'Martillos, destornilladores, alicates, llaves, sierras manuales.',
            ],
            [
                'name' => 'Herramientas eléctricas',
                'description' => 'Taladros, amoladoras, sierras eléctricas, pulidoras.',
            ],
            [
                'name' => 'Electricidad',
                'description' => 'Cables, interruptores, focos, tableros eléctricos, tomacorrientes.',
            ],
            [
                'name' => 'Fontanería',
                'description' => 'Tuberías, válvulas, grifos, conexiones y accesorios sanitarios.',
            ],
            [
                'name' => 'Pinturas y acabados',
                'description' => 'Pinturas, barnices, solventes, rodillos, brochas.',
            ],
            [
                'name' => 'Construcción',
                'description' => 'Cemento, ladrillos, varillas, mallas y agregados.',
            ],
            [
                'name' => 'Fijaciones',
                'description' => 'Clavos, tornillos, pernos, tuercas, arandelas y anclajes.',
            ],
            [
                'name' => 'Seguridad industrial',
                'description' => 'Guantes, cascos, lentes, botas, mascarillas.',
            ],
            [
                'name' => 'Iluminación',
                'description' => 'Focos LED, lámparas, reflectores, apliques.',
            ],
            [
                'name' => 'Abrasivos',
                'description' => 'Lijas, discos de corte, discos flap, bandas abrasivas.',
            ],
            [
                'name' => 'Selladores y adhesivos',
                'description' => 'Siliconas, adhesivos, pegamentos, espumas expansivas.',
            ],
            [
                'name' => 'Ferretería general',
                'description' => 'Bisagras, candados, cerraduras, cadenas, manillas.',
            ],
            [
                'name' => 'Tuberías y accesorios',
                'description' => 'PVC, CPVC, accesorios para desagüe y presión.',
            ],
            [
                'name' => 'Jardinería',
                'description' => 'Tijeras, mangueras, aspersores, herramientas de jardín.',
            ],
            [
                'name' => 'Equipos y maquinaria',
                'description' => 'Compresores, generadores, motobombas.',
            ],
        ];

        // updateOrInsert (idempotente): busca por 'name'; si ya existe no duplica.
        foreach ($categories as $cat) {
            DB::table('product_categories')->updateOrInsert(
                ['name' => $cat['name']],
                [
                    'description' => $cat['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
