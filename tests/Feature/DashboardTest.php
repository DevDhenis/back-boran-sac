<?php

namespace Tests\Feature;

use App\Models\Access;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Creates a verified user whose role holds the given access names and
     * returns a valid JWT for it.
     */
    private function tokenForUserWithAccesses(array $accessNames): string
    {
        $role = Role::create(['name' => 'Rol '.uniqid()]);

        foreach ($accessNames as $name) {
            $access = Access::create(['name' => $name, 'path' => '/'.$name, 'icon' => 'pi']);
            $role->accesses()->attach($access->id);
        }

        $person = Person::create(['first_name' => 'Staff', 'document_number' => uniqid()]);

        $user = User::create([
            'username' => 'staff'.uniqid(),
            'email' => uniqid().'@mail.com',
            'password' => 'secret123',
            'role_id' => $role->id,
            'person_id' => $person->id,
            'email_verified_at' => now(),
        ]);

        return $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->json('token');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/dashboard')->assertStatus(401);
    }

    public function test_dashboard_forbidden_without_panel_access(): void
    {
        $token = $this->tokenForUserWithAccesses(['Catálogo']);

        $this->getJson('/api/dashboard', ['Authorization' => "Bearer {$token}"])
            ->assertStatus(403);
    }

    public function test_dashboard_returns_metrics_with_panel_access(): void
    {
        $token = $this->tokenForUserWithAccesses(['Panel']);

        $this->getJson('/api/dashboard?range=30d', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'range',
                    'kpis' => [
                        'revenue' => ['value', 'delta_pct'],
                        'orders' => ['value', 'delta_pct'],
                        'avg_ticket' => ['value', 'delta_pct'],
                        'low_stock_count' => ['value'],
                    ],
                    'revenue_trend',
                    'orders_by_status',
                    'sales_by_category',
                    'top_products',
                    'payments_by_status',
                    'inventory_movements',
                    'low_stock',
                    'recent_sales',
                    'counts' => ['products', 'employees', 'clients', 'categories'],
                ],
            ]);
    }
}
