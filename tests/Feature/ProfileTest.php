<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileTest extends Testcase
{
    use RefreshDatabase;

    private function authUser(array $personOverrides = [], array $userOverrides = []): array
    {
        $documentType = DocumentType::firstOrCreate(['name' => 'DNI']);
        $role = Role::firstOrCreate(['name' => 'Cliente']);

        $person = Person::create(array_merge([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'second_last_name' => 'Gómez',
            'address' => 'Av. Siempre Viva 123',
            'document_number' => '12345678',
            'document_type_id' => $documentType->id,
        ], $personOverrides));

        $user = User::create(array_merge([
            'username' => 'juanp',
            'email' => 'juan@example.com',
            'password' => 'secret123',
            'role_id' => $role->id,
            'person_id' => $person->id,
        ], $userOverrides));

        $token = JWTAuth::fromUser($user);

        return [$user, $person, $token];
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        [$user, $person, $token] = $this->authUser();

        $response = $this->withToken($token)->getJson('/api/profile');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'username' => 'juanp',
                    'email' => 'juan@example.com',
                    'person' => [
                        'first_name' => 'Juan',
                        'document_number' => '12345678',
                    ],
                ],
            ]);
    }

    public function test_authenticated_user_can_update_essential_personal_data(): void
    {
        [$user, $person, $token] = $this->authUser();

        $response = $this->withToken($token)->postJson('/api/profile/update', [
            'first_name' => 'Juan Carlos',
            'address' => 'Calle Nueva 456',
            'username' => 'juancarlos',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('persons', [
            'id' => $person->id,
            'first_name' => 'Juan Carlos',
            'address' => 'Calle Nueva 456',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'juancarlos',
        ]);
    }

    public function test_update_cannot_change_role_or_password(): void
    {
        [$user, $person, $token] = $this->authUser();
        $originalRoleId = $user->role_id;
        $originalPassword = $user->fresh()->password;

        $this->withToken($token)->postJson('/api/profile/update', [
            'first_name' => 'Otro',
            'role_id' => 999,
            'password' => 'hackeada',
        ])->assertOk();

        $fresh = $user->fresh();
        $this->assertEquals($originalRoleId, $fresh->role_id);
        $this->assertEquals($originalPassword, $fresh->password);
    }

    public function test_update_rejects_duplicate_username(): void
    {
        // Usuario existente que ocupa el username objetivo.
        $this->authUser(
            ['document_number' => '87654321'],
            ['username' => 'ocupado', 'email' => 'otro@example.com']
        );

        [$user, $person, $token] = $this->authUser(
            ['document_number' => '11112222'],
            ['username' => 'libre', 'email' => 'libre@example.com']
        );

        $this->withToken($token)->postJson('/api/profile/update', [
            'username' => 'ocupado',
        ])->assertStatus(422);
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/profile')->assertStatus(401);
    }

    public function test_document_types_list_returns_active_types(): void
    {
        [$user, $person, $token] = $this->authUser();
        DocumentType::create(['name' => 'RUC']);
        DocumentType::create(['name' => 'Carnet', 'status' => 'I']); // inactivo: no debe listarse

        $response = $this->withToken($token)->getJson('/api/document-types');

        $response->assertOk()->assertJson(['success' => true]);
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('RUC'));
        $this->assertFalse($names->contains('Carnet'));
    }
}
