<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_account_and_saves_address(): void
    {
        Role::create(['name' => 'Administrador General']); // id 1
        Role::create(['name' => 'Cliente']);               // id 2 (rol por defecto del registro)

        $payload = [
            'first_name' => 'dhenis',
            'last_name' => 'aguado',
            'second_last_name' => 'garcai',
            'address' => 'asdasd',
            'username' => 'stray',
            'email' => 'dhenis@mail.com',
            'password' => '1@Dhenis',
            'password_confirmation' => '1@Dhenis',
        ];

        $this->postJson('/api/auth/register', $payload)
            ->assertStatus(201)
            ->assertJson(['success' => true, 'requires_verification' => true])
            ->assertJsonMissing(['token' => true]);

        $this->assertDatabaseHas('persons', [
            'first_name' => 'dhenis',
            'address' => 'asdasd',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'stray',
            'email' => 'dhenis@mail.com',
            'email_verified_at' => null,
        ]);
    }

    public function test_register_rejects_old_spanish_field_names(): void
    {
        Role::create(['name' => 'Administrador General']);
        Role::create(['name' => 'Cliente']);

        // Payload viejo (nombres en español) -> falta first_name requerido -> 422
        $this->postJson('/api/auth/register', [
            'nombres' => 'dhenis',
            'username' => 'stray',
            'email' => 'dhenis@mail.com',
            'password' => '1@Dhenis',
            'password_confirmation' => '1@Dhenis',
        ])->assertStatus(422);
    }
}
