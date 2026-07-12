<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $persons = DB::table('persons')
            ->whereIn('document_number', [
                '88888888',
                '77777777',
            ])->get();

        foreach ($persons as $p) {
            DB::table('clients')->updateOrInsert(
                ['person_id' => $p->id],
                [
                    'total_purchases' => 0,
                    'accepted_purchases' => 0,
                    'rejected_purchases' => 0,
                    'returned_purchases' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
