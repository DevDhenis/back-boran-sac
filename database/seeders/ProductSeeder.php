<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Unit;
use App\Models\ProductCategory;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $u   = Unit::where('abreviatura', 'u')->value('id');
        $L   = Unit::where('abreviatura', 'L')->value('id');
        $m   = Unit::where('abreviatura', 'm')->value('id');
        $paq = Unit::where('abreviatura', 'paq')->value('id');
        $bar = Unit::where('abreviatura', 'bar')->value('id');

        $catManual     = ProductCategory::where('nombre', 'Herramientas manuales')->value('id');
        $catPinturas   = ProductCategory::where('nombre', 'Pinturas y acabados')->value('id');
        $catElectric   = ProductCategory::where('nombre', 'Electricidad')->value('id');
        $catFijaciones = ProductCategory::where('nombre', 'Fijaciones')->value('id');
        $catConstru    = ProductCategory::where('nombre', 'Construcción')->value('id');

        $products = [

            // -----------------------------------------------------------
            // 1. Herramienta manual (Unidad)
            // -----------------------------------------------------------
            [
                'codigo_interno'       => 'FER-001-A1',
                'nombre'               => 'Martillo de carpintero 16 oz',
                'descripcion'          => 'Martillo profesional fabricado en acero forjado, mango antideslizante y amortiguador de impacto.',
                'imagen'               => 'https://promart.vteximg.com.br/arquivos/ids/9115533-700-700/91113.jpg?v=638882939765870000',
                'stock'                => 45,
                'cantidad_minima'      => 3,
                'en_promocion'         => false,
                'pre_uni'              => 32.90,
                'pre_uni_may'          => 29.50,
                'can_min_may'          => 8,
                'descuento'            => 0,
                'unit_id'              => $u,
                'product_category_id'  => $catManual,
            ],

            // -----------------------------------------------------------
            // 2. Pintura (Litro)
            // -----------------------------------------------------------
            [
                'codigo_interno'       => 'FER-002-P1',
                'nombre'               => 'Pintura acrílica blanca 1L',
                'descripcion'          => 'Pintura acrílica blanca de alta cobertura, ideal para interiores. Acabado mate y secado rápido.',
                'imagen'               => 'https://m.media-amazon.com/images/I/61caDZVh-rL._AC_UF894,1000_QL80_.jpg',
                'stock'                => 0, // ESTE ES EL PRODUCTO SIN STOCK
                'cantidad_minima'      => 5,
                'en_promocion'         => true,
                'pre_uni'              => 22.50,
                'pre_uni_may'          => 19.80,
                'can_min_may'          => 6,
                'descuento'            => 10,
                'unit_id'              => $L,
                'product_category_id'  => $catPinturas,
            ],

            // -----------------------------------------------------------
            // 3. Cable eléctrico (Metro)
            // -----------------------------------------------------------
            [
                'codigo_interno'       => 'FER-003-E1',
                'nombre'               => 'Cable eléctrico de cobre 3mm',
                'descripcion'          => 'Cable de cobre aislado con recubrimiento PVC, ideal para instalaciones eléctricas residenciales.',
                'imagen'               => 'https://image.made-in-china.com/202f0j00QUzfWaSYjspu/Electric-Cord-Wire-and-3mm-300mcm-300mcm-4-Core-Multimode-Fiber-Optic-Cable-Copper-or-Alminum-Motorcycle-Control-Cable-Power-Cable.webp',
                'stock'                => 250,
                'cantidad_minima'      => 15,
                'en_promocion'         => false,
                'pre_uni'              => 3.90,
                'pre_uni_may'          => 3.50,
                'can_min_may'          => 25,
                'descuento'            => 0,
                'unit_id'              => $m,
                'product_category_id'  => $catElectric,
            ],

            // -----------------------------------------------------------
            // 4. Tornillos (Paquete)
            // -----------------------------------------------------------
            [
                'codigo_interno'       => 'FER-004-F2',
                'nombre'               => 'Tornillos autorroscantes 1" (paquete de 100)',
                'descripcion'          => 'Paquete de 100 tornillos autorroscantes galvanizados, ideales para fijación en láminas metálicas.',
                'imagen'               => 'https://media.falabella.com/sodimacPE/172723_01/w=800,h=800,fit=pad',
                'stock'                => 80,
                'cantidad_minima'      => 5,
                'en_promocion'         => false,
                'pre_uni'              => 11.00,
                'pre_uni_may'          => 9.80,
                'can_min_may'          => 10,
                'descuento'            => 0,
                'unit_id'              => $paq,
                'product_category_id'  => $catFijaciones,
            ],

            // -----------------------------------------------------------
            // 5. Varilla (Barra)
            // -----------------------------------------------------------
            [
                'codigo_interno'       => 'FER-005-C8',
                'nombre'               => 'Varilla de acero corrugado 1/2"',
                'descripcion'          => 'Varilla corrugada de acero estructural, utilizada para refuerzos de concreto y construcciones pesadas.',
                'imagen'               => 'https://www.dsurco.com/wp-content/uploads/2022/09/varilla-fierro-aceros-arequipa.jpg',
                'stock'                => 120,
                'cantidad_minima'      => 10,
                'en_promocion'         => true,
                'pre_uni'              => 16.90,
                'pre_uni_may'          => 15.20,
                'can_min_may'          => 20,
                'descuento'            => 5,
                'unit_id'              => $bar,
                'product_category_id'  => $catConstru,
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['codigo_interno' => $p['codigo_interno']],
                array_merge($p, ['estado_registro' => 'A'])
            );
        }
    }
}
