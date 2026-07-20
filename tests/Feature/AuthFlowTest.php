<?php

namespace Tests\Feature;

use App\Mail\VerificationCodeMail;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(bool $verified, array $overrides = []): User
    {
        $role = Role::firstOrCreate(['name' => 'Cliente']);
        $person = Person::create(['first_name' => 'Test', 'document_number' => uniqid()]);

        return User::create(array_merge([
            'username' => 'tester'.uniqid(),
            'email' => uniqid().'@mail.com',
            'password' => 'secret123',
            'role_id' => $role->id,
            'person_id' => $person->id,
            'email_verified_at' => $verified ? now() : null,
            'verification_code' => $verified ? null : 'ABC12345',
        ], $overrides));
    }

    public function test_unverified_user_cannot_login(): void
    {
        $user = $this->makeUser(verified: false);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'requires_verification' => true]);
    }

    public function test_verified_user_can_login(): void
    {
        $user = $this->makeUser(verified: true);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['token']);
    }

    public function test_verify_email_is_public_and_verifies(): void
    {
        $user = $this->makeUser(verified: false, overrides: ['verification_code' => 'CODE1234']);

        // Sin token (endpoint público): email + code.
        $this->postJson('/api/auth/verify-email', ['email' => $user->email, 'code' => 'CODE1234'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_resend_code_regenerates_and_sends_mail(): void
    {
        Mail::fake();
        $user = $this->makeUser(verified: false, overrides: ['verification_code' => 'OLDCODE1']);

        $this->postJson('/api/auth/resend-code', ['email' => $user->email])
            ->assertOk()
            ->assertJson(['success' => true]);

        Mail::assertSent(VerificationCodeMail::class);
        $this->assertNotEquals('OLDCODE1', $user->fresh()->verification_code);
    }

    public function test_resend_code_conflict_if_already_verified(): void
    {
        $user = $this->makeUser(verified: true);

        $this->postJson('/api/auth/resend-code', ['email' => $user->email])
            ->assertStatus(409)
            ->assertJson(['success' => false]);
    }
}
