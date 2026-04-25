<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
  public function run(): void
  {
    $persons = DB::table('persons')
      ->whereIn('numero_documento', [
        '88888888',
        '77777777'
      ])->get();

    foreach ($persons as $p) {
      DB::table('clients')->updateOrInsert(
        ['person_id' => $p->id],
        [
          'cantidad_compras' => 0,
          'cantidad_compras_aceptadas' => 0,
          'cantidad_compras_rechazadas' => 0,
          'cantidad_compras_devueltas' => 0,
          'created_at' => now(),
          'updated_at' => now(),
        ]
      );
    }
  }
}
