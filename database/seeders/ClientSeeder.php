<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clientPerson = DB::table('persons')
            ->where('document_number', '88888888')
            ->first();

        if (! $clientPerson) {
            return;
        }

        DB::table('clients')->updateOrInsert(
            ['person_id' => $clientPerson->id],
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
