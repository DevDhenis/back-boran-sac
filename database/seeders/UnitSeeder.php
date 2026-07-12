<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrInsert (idempotente): busca por 'nombre'; si ya existe no duplica.
        $units = [
            ['nombre' => 'Unidad',          'abreviatura' => 'u'],
            ['nombre' => 'Litro',           'abreviatura' => 'L'],
            ['nombre' => 'Mililitro',       'abreviatura' => 'ml'],
            ['nombre' => 'Galón',           'abreviatura' => 'gal'],
            ['nombre' => 'Kilogramo',       'abreviatura' => 'kg'],
            ['nombre' => 'Gramo',           'abreviatura' => 'g'],
            ['nombre' => 'Metro',           'abreviatura' => 'm'],
            ['nombre' => 'Centímetro',      'abreviatura' => 'cm'],
            ['nombre' => 'Milímetro',       'abreviatura' => 'mm'],
            ['nombre' => 'Metro cuadrado',  'abreviatura' => 'm²'],
            ['nombre' => 'Metro cúbico',    'abreviatura' => 'm³'],
            ['nombre' => 'Caja',            'abreviatura' => 'cj'],
            ['nombre' => 'Paquete',         'abreviatura' => 'paq'],
            ['nombre' => 'Par',             'abreviatura' => 'par'],
            ['nombre' => 'Juego',           'abreviatura' => 'set'],
            ['nombre' => 'Barra',           'abreviatura' => 'bar'],
            ['nombre' => 'Rollo',           'abreviatura' => 'roll'],
            ['nombre' => 'Tubo',            'abreviatura' => 'tb'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->updateOrInsert(
                ['nombre' => $unit['nombre']],
                [
                    'abreviatura' => $unit['abreviatura'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }
}
