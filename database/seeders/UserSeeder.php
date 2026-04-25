<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 🧑 Admin
        $adminPerson = DB::table('persons')->where('numero_documento', '99999999')->first();
        if (!$adminPerson) {
            throw new RuntimeException('Persona admin no encontrada');
        }

        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            [
                'password' => Hash::make('admins'),
                'email' => 'admin@example.com',
                'email_verified_at' => now()->toDateTimeString(),
                'codigo_verificacion' => Str::upper(Str::random(8)),
                'estado_registro' => 'A',
                'role_id' => 1, // admin
                'person_id' => $adminPerson->id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 👤 Cliente
        $clientPerson = DB::table('persons')->where('numero_documento', '88888888')->first();
        if (!$clientPerson) {
            throw new RuntimeException('Persona cliente no encontrada');
        }

        DB::table('users')->updateOrInsert(
            ['username' => 'cliente'],
            [
                'password' => Hash::make('cliente'),
                'email' => 'cliente@example.com',
                'email_verified_at' => now()->toDateTimeString(),
                'codigo_verificacion' => Str::upper(Str::random(8)),
                'estado_registro' => 'A',
                'role_id' => 2, // cliente
                'person_id' => $clientPerson->id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 👤 Cliente 2
        $clientPerson2 = DB::table('persons')->where('numero_documento', '77777777')->first();
        if (!$clientPerson2) {
            throw new RuntimeException('Persona cliente 2 no encontrada');
        }

        DB::table('users')->updateOrInsert(
            ['username' => 'cliente2'],
            [
                'password' => Hash::make('cliente2'),
                'email' => 'cliente2@example.com',
                'email_verified_at' => now()->toDateTimeString(),
                'codigo_verificacion' => Str::upper(Str::random(8)),
                'estado_registro' => 'A',
                'role_id' => 2, // cliente
                'person_id' => $clientPerson2->id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
