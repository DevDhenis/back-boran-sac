<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrInsert (idempotente): busca por 'name'; si ya existe no duplica.
        $units = [
            ['name' => 'Unidad',          'abbreviation' => 'u'],
            ['name' => 'Litro',           'abbreviation' => 'L'],
            ['name' => 'Mililitro',       'abbreviation' => 'ml'],
            ['name' => 'Galón',           'abbreviation' => 'gal'],
            ['name' => 'Kilogramo',       'abbreviation' => 'kg'],
            ['name' => 'Gramo',           'abbreviation' => 'g'],
            ['name' => 'Metro',           'abbreviation' => 'm'],
            ['name' => 'Centímetro',      'abbreviation' => 'cm'],
            ['name' => 'Milímetro',       'abbreviation' => 'mm'],
            ['name' => 'Metro cuadrado',  'abbreviation' => 'm²'],
            ['name' => 'Metro cúbico',    'abbreviation' => 'm³'],
            ['name' => 'Caja',            'abbreviation' => 'cj'],
            ['name' => 'Paquete',         'abbreviation' => 'paq'],
            ['name' => 'Par',             'abbreviation' => 'par'],
            ['name' => 'Juego',           'abbreviation' => 'set'],
            ['name' => 'Barra',           'abbreviation' => 'bar'],
            ['name' => 'Rollo',           'abbreviation' => 'roll'],
            ['name' => 'Tubo',            'abbreviation' => 'tb'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->updateOrInsert(
                ['name' => $unit['name']],
                [
                    'abbreviation' => $unit['abbreviation'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
