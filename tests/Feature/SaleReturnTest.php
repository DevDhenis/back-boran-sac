<?php

namespace Tests\Feature;

use App\Models\Access;
use App\Models\Client;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleReturnTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user): string
    {
        return $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->json('token');
    }

    private function makeUser(array $accessNames = [], bool $asClient = false): array
    {
        $role = Role::create(['name' => 'Rol '.uniqid()]);
        foreach ($accessNames as $name) {
            $access = Access::create(['name' => $name, 'path' => '/'.$name, 'icon' => 'pi']);
            $role->accesses()->attach($access->id);
        }

        $person = Person::create(['first_name' => 'Test', 'document_number' => uniqid()]);
        $client = null;
        if ($asClient) {
            $client = Client::create([
                'person_id' => $person->id,
                'total_purchases' => 0, 'accepted_purchases' => 0,
                'rejected_purchases' => 0, 'returned_purchases' => 0,
            ]);
        }

        $user = User::create([
            'username' => 'u'.uniqid(),
            'email' => uniqid().'@mail.com',
            'password' => 'secret123',
            'role_id' => $role->id,
            'person_id' => $person->id,
            'email_verified_at' => now(),
        ]);

        return [$this->login($user), $client];
    }

    private function makeProduct(float $stock = 100): Product
    {
        $unit = Unit::create(['name' => 'Unidad', 'abbreviation' => 'u']);
        $category = ProductCategory::create(['name' => 'Cat '.uniqid()]);

        return Product::create([
            'internal_code' => 'T'.uniqid(), 'name' => 'Producto', 'stock' => $stock,
            'minimum_quantity' => 5, 'on_promotion' => false, 'unit_price' => 10,
            'wholesale_unit_price' => 9, 'wholesale_min_quantity' => 10, 'discount' => 0,
            'unit_id' => $unit->id, 'product_category_id' => $category->id, 'status' => 'A', 'final_price' => 10,
        ]);
    }

    private function makeSale(Client $client, Product $product, int $qty, string $status): array
    {
        $sale = Sale::create([
            'customer_id' => $client->id, 'employee_id' => null, 'sale_date' => now(),
            'status' => $status, 'subtotal' => 10 * $qty, 'tax' => 0, 'total' => 10 * $qty,
        ]);
        $item = SalesItem::create([
            'sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => $qty,
            'price' => 10, 'discount' => 0, 'subtotal' => 10 * $qty,
        ]);

        return [$sale, $item];
    }

    public function test_client_can_request_return_of_delivered_sale(): void
    {
        [$token, $client] = $this->makeUser(asClient: true);
        $product = $this->makeProduct();
        [$sale, $item] = $this->makeSale($client, $product, 5, 'delivered');

        $this->postJson('/api/returns', [
            'sale_id' => $sale->id,
            'reason' => 'Producto llegó fallado',
            'items' => [['sales_item_id' => $item->id, 'quantity' => 2]],
        ], ['Authorization' => "Bearer {$token}"])
            ->assertCreated()
            ->assertJson(['success' => true, 'data' => ['status' => 'requested']]);

        $this->assertDatabaseHas('sale_returns', ['sale_id' => $sale->id, 'status' => 'requested']);
    }

    public function test_cannot_return_more_than_purchased(): void
    {
        [$token, $client] = $this->makeUser(asClient: true);
        $product = $this->makeProduct();
        [$sale, $item] = $this->makeSale($client, $product, 3, 'delivered');

        $this->postJson('/api/returns', [
            'sale_id' => $sale->id, 'reason' => 'x',
            'items' => [['sales_item_id' => $item->id, 'quantity' => 10]],
        ], ['Authorization' => "Bearer {$token}"])->assertStatus(422);
    }

    public function test_cannot_return_non_delivered_sale(): void
    {
        [$token, $client] = $this->makeUser(asClient: true);
        $product = $this->makeProduct();
        [$sale, $item] = $this->makeSale($client, $product, 5, 'pending_shipment');

        $this->postJson('/api/returns', [
            'sale_id' => $sale->id, 'reason' => 'x',
            'items' => [['sales_item_id' => $item->id, 'quantity' => 1]],
        ], ['Authorization' => "Bearer {$token}"])->assertStatus(422);
    }

    public function test_staff_approve_restores_stock(): void
    {
        [$clientToken, $client] = $this->makeUser(asClient: true);
        [$staffToken] = $this->makeUser(['Devoluciones']);
        $product = $this->makeProduct(50);
        [$sale, $item] = $this->makeSale($client, $product, 5, 'delivered');

        $returnId = $this->postJson('/api/returns', [
            'sale_id' => $sale->id, 'reason' => 'fallado',
            'items' => [['sales_item_id' => $item->id, 'quantity' => 2]],
        ], ['Authorization' => "Bearer {$clientToken}"])->json('data.id');

        $this->postJson("/api/returns/{$returnId}/approve", [], ['Authorization' => "Bearer {$staffToken}"])
            ->assertOk()
            ->assertJson(['data' => ['status' => 'approved']]);

        $this->assertEquals(52, $product->fresh()->stock); // 50 + 2 restored
        $this->assertDatabaseHas('inventory_management', [
            'sale_return_id' => $returnId,
            'origin' => 'customer_return',
            'movement_type' => 'inbound',
        ]);
    }

    public function test_refund_only_after_approved(): void
    {
        [$clientToken, $client] = $this->makeUser(asClient: true);
        [$staffToken] = $this->makeUser(['Devoluciones']);
        $product = $this->makeProduct(50);
        [$sale, $item] = $this->makeSale($client, $product, 5, 'delivered');

        $returnId = $this->postJson('/api/returns', [
            'sale_id' => $sale->id, 'reason' => 'x',
            'items' => [['sales_item_id' => $item->id, 'quantity' => 1]],
        ], ['Authorization' => "Bearer {$clientToken}"])->json('data.id');

        // Not approved yet -> refund rejected.
        $this->postJson("/api/returns/{$returnId}/refund", [], ['Authorization' => "Bearer {$staffToken}"])
            ->assertStatus(422);

        $this->postJson("/api/returns/{$returnId}/approve", [], ['Authorization' => "Bearer {$staffToken}"])->assertOk();
        $this->postJson("/api/returns/{$returnId}/refund", [], ['Authorization' => "Bearer {$staffToken}"])
            ->assertOk()
            ->assertJson(['data' => ['refund_status' => 'refunded']]);
    }
}
