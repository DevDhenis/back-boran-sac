<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('units')->insert([
            [
                'nombre' => 'Unidad',
                'abreviatura' => 'u',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Litro',
                'abreviatura' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Mililitro',
                'abreviatura' => 'ml',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Galón',
                'abreviatura' => 'gal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Kilogramo',
                'abreviatura' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Gramo',
                'abreviatura' => 'g',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Metro',
                'abreviatura' => 'm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Centímetro',
                'abreviatura' => 'cm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Milímetro',
                'abreviatura' => 'mm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Metro cuadrado',
                'abreviatura' => 'm²',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Metro cúbico',
                'abreviatura' => 'm³',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Caja',
                'abreviatura' => 'cj',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Paquete',
                'abreviatura' => 'paq',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Par',
                'abreviatura' => 'par',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Juego',
                'abreviatura' => 'set',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Barra',
                'abreviatura' => 'bar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Rollo',
                'abreviatura' => 'roll',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Tubo',
                'abreviatura' => 'tb',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
