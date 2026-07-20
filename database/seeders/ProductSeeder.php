<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $u = Unit::where('abbreviation', 'u')->value('id');
        $L = Unit::where('abbreviation', 'L')->value('id');
        $m = Unit::where('abbreviation', 'm')->value('id');
        $paq = Unit::where('abbreviation', 'paq')->value('id');
        $bar = Unit::where('abbreviation', 'bar')->value('id');

        $catManual = ProductCategory::where('name', 'Herramientas manuales')->value('id');
        $catPinturas = ProductCategory::where('name', 'Pinturas y acabados')->value('id');
        $catElectric = ProductCategory::where('name', 'Electricidad')->value('id');
        $catFijaciones = ProductCategory::where('name', 'Fijaciones')->value('id');
        $catConstru = ProductCategory::where('name', 'Construcción')->value('id');

        $products = [

            // -----------------------------------------------------------
            // 1. Herramienta manual (Unidad)
            // -----------------------------------------------------------
            [
                'internal_code' => 'FER-001-A1',
                'name' => 'Martillo de carpintero 16 oz',
                'description' => 'Martillo profesional fabricado en acero forjado, mango antideslizante y amortiguador de impacto.',
                'image' => 'https://promart.vteximg.com.br/arquivos/ids/9115533-700-700/91113.jpg?v=638882939765870000',
                'stock' => 45,
                'minimum_quantity' => 3,
                'on_promotion' => false,
                'unit_price' => 32.90,
                'wholesale_unit_price' => 29.50,
                'wholesale_min_quantity' => 8,
                'discount' => 0,
                'unit_id' => $u,
                'product_category_id' => $catManual,
            ],

            // -----------------------------------------------------------
            // 2. Pintura (Litro)
            // -----------------------------------------------------------
            [
                'internal_code' => 'FER-002-P1',
                'name' => 'Pintura acrílica blanca 1L',
                'description' => 'Pintura acrílica blanca de alta cobertura, ideal para interiores. Acabado mate y secado rápido.',
                'image' => 'https://m.media-amazon.com/images/I/61caDZVh-rL._AC_UF894,1000_QL80_.jpg',
                'stock' => 0, // ESTE ES EL PRODUCTO SIN STOCK
                'minimum_quantity' => 5,
                'on_promotion' => true,
                'unit_price' => 22.50,
                'wholesale_unit_price' => 19.80,
                'wholesale_min_quantity' => 6,
                'discount' => 10,
                'unit_id' => $L,
                'product_category_id' => $catPinturas,
            ],

            // -----------------------------------------------------------
            // 3. Cable eléctrico (Metro)
            // -----------------------------------------------------------
            [
                'internal_code' => 'FER-003-E1',
                'name' => 'Cable eléctrico de cobre 3mm',
                'description' => 'Cable de cobre aislado con recubrimiento PVC, ideal para instalaciones eléctricas residenciales.',
                'image' => 'https://image.made-in-china.com/202f0j00QUzfWaSYjspu/Electric-Cord-Wire-and-3mm-300mcm-300mcm-4-Core-Multimode-Fiber-Optic-Cable-Copper-or-Alminum-Motorcycle-Control-Cable-Power-Cable.webp',
                'stock' => 250,
                'minimum_quantity' => 15,
                'on_promotion' => false,
                'unit_price' => 3.90,
                'wholesale_unit_price' => 3.50,
                'wholesale_min_quantity' => 25,
                'discount' => 0,
                'unit_id' => $m,
                'product_category_id' => $catElectric,
            ],

            // -----------------------------------------------------------
            // 4. Tornillos (Paquete)
            // -----------------------------------------------------------
            [
                'internal_code' => 'FER-004-F2',
                'name' => 'Tornillos autorroscantes 1" (paquete de 100)',
                'description' => 'Paquete de 100 tornillos autorroscantes galvanizados, ideales para fijación en láminas metálicas.',
                'image' => 'https://media.falabella.com/sodimacPE/172723_01/w=800,h=800,fit=pad',
                'stock' => 80,
                'minimum_quantity' => 5,
                'on_promotion' => false,
                'unit_price' => 11.00,
                'wholesale_unit_price' => 9.80,
                'wholesale_min_quantity' => 10,
                'discount' => 0,
                'unit_id' => $paq,
                'product_category_id' => $catFijaciones,
            ],

            // -----------------------------------------------------------
            // 5. Varilla (Barra)
            // -----------------------------------------------------------
            [
                'internal_code' => 'FER-005-C8',
                'name' => 'Varilla de acero corrugado 1/2"',
                'description' => 'Varilla corrugada de acero estructural, utilizada para refuerzos de concreto y construcciones pesadas.',
                'image' => 'https://www.dsurco.com/wp-content/uploads/2022/09/varilla-fierro-aceros-arequipa.jpg',
                'stock' => 120,
                'minimum_quantity' => 10,
                'on_promotion' => true,
                'unit_price' => 16.90,
                'wholesale_unit_price' => 15.20,
                'wholesale_min_quantity' => 20,
                'discount' => 5,
                'unit_id' => $bar,
                'product_category_id' => $catConstru,
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['internal_code' => $p['internal_code']],
                array_merge($p, ['status' => 'A'])
            );
        }
    }
}
