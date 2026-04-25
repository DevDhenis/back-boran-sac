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
                'nombre' => 'Herramientas manuales',
                'descripcion' => 'Martillos, destornilladores, alicates, llaves, sierras manuales.',
            ],
            [
                'nombre' => 'Herramientas eléctricas',
                'descripcion' => 'Taladros, amoladoras, sierras eléctricas, pulidoras.',
            ],
            [
                'nombre' => 'Electricidad',
                'descripcion' => 'Cables, interruptores, focos, tableros eléctricos, tomacorrientes.',
            ],
            [
                'nombre' => 'Fontanería',
                'descripcion' => 'Tuberías, válvulas, grifos, conexiones y accesorios sanitarios.',
            ],
            [
                'nombre' => 'Pinturas y acabados',
                'descripcion' => 'Pinturas, barnices, solventes, rodillos, brochas.',
            ],
            [
                'nombre' => 'Construcción',
                'descripcion' => 'Cemento, ladrillos, varillas, mallas y agregados.',
            ],
            [
                'nombre' => 'Fijaciones',
                'descripcion' => 'Clavos, tornillos, pernos, tuercas, arandelas y anclajes.',
            ],
            [
                'nombre' => 'Seguridad industrial',
                'descripcion' => 'Guantes, cascos, lentes, botas, mascarillas.',
            ],
            [
                'nombre' => 'Iluminación',
                'descripcion' => 'Focos LED, lámparas, reflectores, apliques.',
            ],
            [
                'nombre' => 'Abrasivos',
                'descripcion' => 'Lijas, discos de corte, discos flap, bandas abrasivas.',
            ],
            [
                'nombre' => 'Selladores y adhesivos',
                'descripcion' => 'Siliconas, adhesivos, pegamentos, espumas expansivas.',
            ],
            [
                'nombre' => 'Ferretería general',
                'descripcion' => 'Bisagras, candados, cerraduras, cadenas, manillas.',
            ],
            [
                'nombre' => 'Tuberías y accesorios',
                'descripcion' => 'PVC, CPVC, accesorios para desagüe y presión.',
            ],
            [
                'nombre' => 'Jardinería',
                'descripcion' => 'Tijeras, mangueras, aspersores, herramientas de jardín.',
            ],
            [
                'nombre' => 'Equipos y maquinaria',
                'descripcion' => 'Compresores, generadores, motobombas.',
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('product_categories')->insert([
                'nombre' => $cat['nombre'],
                'descripcion' => $cat['descripcion'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
