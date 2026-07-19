<?php

namespace Tests\Feature;

use App\Models\Access;
use App\Models\Person;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private function token(array $accessNames = []): string
    {
        $role = Role::create(['name' => 'Rol '.uniqid()]);
        foreach ($accessNames as $name) {
            $access = Access::create(['name' => $name, 'path' => '/'.$name, 'icon' => 'pi']);
            $role->accesses()->attach($access->id);
        }
        $person = Person::create(['first_name' => 'Test', 'document_number' => uniqid()]);
        $user = User::create([
            'username' => 'u'.uniqid(), 'email' => uniqid().'@mail.com', 'password' => 'secret123',
            'role_id' => $role->id, 'person_id' => $person->id, 'email_verified_at' => now(),
        ]);

        return $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret123'])->json('token');
    }

    public function test_requires_proveedores_access(): void
    {
        $token = $this->token(); // no access
        $this->getJson('/api/suppliers', ['Authorization' => "Bearer {$token}"])->assertStatus(403);
    }

    public function test_staff_can_create_supplier(): void
    {
        $token = $this->token(['Proveedores']);

        $this->postJson('/api/suppliers', [
            'name' => 'Distribuidora Andina',
            'ruc' => '20123456789',
            'phone' => '999888777',
            'email' => 'ventas@andina.pe',
        ], ['Authorization' => "Bearer {$token}"])
            ->assertCreated()
            ->assertJson(['success' => true, 'data' => ['name' => 'Distribuidora Andina', 'status' => 'A']]);

        $this->assertDatabaseHas('suppliers', ['ruc' => '20123456789']);
    }

    public function test_ruc_must_have_11_digits(): void
    {
        $token = $this->token(['Proveedores']);
        $this->postJson('/api/suppliers', ['name' => 'X', 'ruc' => '123'], ['Authorization' => "Bearer {$token}"])
            ->assertStatus(422);
    }

    public function test_can_update_and_delete_supplier(): void
    {
        $token = $this->token(['Proveedores']);
        $auth = ['Authorization' => "Bearer {$token}"];
        $supplier = Supplier::create(['name' => 'Antiguo', 'status' => 'A']);

        $this->putJson("/api/suppliers/{$supplier->id}", ['name' => 'Nuevo Nombre'], $auth)
            ->assertOk()
            ->assertJson(['data' => ['name' => 'Nuevo Nombre']]);

        $this->deleteJson("/api/suppliers/{$supplier->id}", [], $auth)->assertOk();
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
