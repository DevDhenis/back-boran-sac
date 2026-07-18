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

class InventoryMovementTest extends TestCase
{
    use RefreshDatabase;

    private function token(array $accessNames = []): string
    {
        $role = Role::create(['name' => 'Rol '.uniqid()]);

        foreach ($accessNames as $name) {
            $access = Access::create(['name' => $name, 'path' => '/'.$name, 'icon' => 'pi']);
            $role->accesses()->attach($access->id);
        }

        $person = Person::create(['first_name' => 'Inv', 'document_number' => uniqid()]);
        $user = User::create([
            'username' => 'inv'.uniqid(),
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

    private function makeProduct(float $stock = 100): Product
    {
        $unit = Unit::create(['name' => 'Unidad', 'abbreviation' => 'u']);
        $category = ProductCategory::create(['name' => 'Cat '.uniqid()]);

        return Product::create([
            'internal_code' => 'T'.uniqid(),
            'name' => 'Producto de prueba',
            'description' => 'x',
            'stock' => $stock,
            'minimum_quantity' => 5,
            'on_promotion' => false,
            'unit_price' => 10,
            'wholesale_unit_price' => 9,
            'wholesale_min_quantity' => 10,
            'discount' => 0,
            'unit_id' => $unit->id,
            'product_category_id' => $category->id,
            'status' => 'A',
            'final_price' => 10,
        ]);
    }

    public function test_inbound_increases_stock(): void
    {
        $token = $this->token();
        $product = $this->makeProduct(100);

        $this->postJson('/api/inventory-movements', [
            'product_id' => $product->id,
            'movement_type' => 'inbound',
            'quantity' => 20,
            'reason' => 'Compra',
        ], ['Authorization' => "Bearer {$token}"])
            ->assertCreated()
            ->assertJson(['success' => true, 'data' => ['stock_before' => 100, 'stock_after' => 120]]);

        $this->assertEquals(120, $product->fresh()->stock);
    }

    public function test_manual_movement_rejects_return_type(): void
    {
        $token = $this->token();
        $product = $this->makeProduct(50);

        // Returns are no longer a manual movement type (they come from the sales flow).
        $this->postJson('/api/inventory-movements', [
            'product_id' => $product->id,
            'movement_type' => 'return',
            'quantity' => 5,
        ], ['Authorization' => "Bearer {$token}"])
            ->assertStatus(422);
    }

    public function test_cancelling_sale_restores_stock_via_movement(): void
    {
        $token = $this->token(['Ventas']); // change-status is behind access:ventas
        $product = $this->makeProduct(100);

        // Simulate the post-checkout state: a pending sale that already deducted stock.
        $person = Person::create(['first_name' => 'Cli', 'document_number' => uniqid()]);
        $client = Client::create([
            'person_id' => $person->id,
            'total_purchases' => 0,
            'accepted_purchases' => 0,
            'rejected_purchases' => 0,
            'returned_purchases' => 0,
        ]);
        $sale = Sale::create([
            'customer_id' => $client->id,
            'employee_id' => null,
            'sale_date' => now(),
            'status' => 'pending_shipment',
            'subtotal' => 100,
            'tax' => 18,
            'total' => 118,
        ]);
        SalesItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 10,
            'discount' => 0,
            'subtotal' => 100,
        ]);
        $product->update(['stock' => 90]);

        $this->postJson("/api/sales/{$sale->id}/change-status", [
            'new_status' => 'cancelled',
        ], ['Authorization' => "Bearer {$token}"])->assertOk();

        // Stock restored and a traceable cancellation movement was posted.
        $this->assertEquals(100, $product->fresh()->stock);
        $this->assertDatabaseHas('inventory_management', [
            'sale_id' => $sale->id,
            'origin' => 'sale_cancellation',
            'movement_type' => 'inbound',
            'stock_after' => 100,
        ]);
    }

    public function test_outbound_over_stock_is_rejected(): void
    {
        $token = $this->token();
        $product = $this->makeProduct(3);

        $this->postJson('/api/inventory-movements', [
            'product_id' => $product->id,
            'movement_type' => 'outbound',
            'quantity' => 10,
        ], ['Authorization' => "Bearer {$token}"])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertEquals(3, $product->fresh()->stock);
    }

    public function test_only_latest_movement_can_be_voided(): void
    {
        $token = $this->token();
        $product = $this->makeProduct(100);
        $auth = ['Authorization' => "Bearer {$token}"];

        $first = $this->postJson('/api/inventory-movements', [
            'product_id' => $product->id, 'movement_type' => 'inbound', 'quantity' => 10,
        ], $auth)->json('data.id');

        $this->postJson('/api/inventory-movements', [
            'product_id' => $product->id, 'movement_type' => 'inbound', 'quantity' => 5,
        ], $auth);

        // Voiding the older movement is rejected (append-only ledger).
        $this->deleteJson("/api/inventory-movements/{$first}", [], $auth)
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    private function productPayload(array $overrides = []): array
    {
        $unit = Unit::create(['name' => 'Unidad', 'abbreviation' => 'u']);
        $category = ProductCategory::create(['name' => 'Cat '.uniqid()]);

        return array_merge([
            'internal_code' => 'P'.uniqid(),
            'name' => 'Nuevo producto',
            'stock' => 30,
            'minimum_quantity' => 5,
            'on_promotion' => false,
            'unit_price' => 10,
            'wholesale_unit_price' => 9,
            'wholesale_min_quantity' => 10,
            'discount' => 0,
            'unit_id' => $unit->id,
            'product_category_id' => $category->id,
        ], $overrides);
    }

    public function test_new_product_is_created_with_zero_stock(): void
    {
        $token = $this->token();
        // Even if a stock value is sent, the product must start at 0 (strict ledger).
        $payload = $this->productPayload(['stock' => 30]);

        $this->postJson('/api/products', $payload, ['Authorization' => "Bearer {$token}"])
            ->assertCreated();

        $product = Product::where('internal_code', $payload['internal_code'])->firstOrFail();

        $this->assertEquals(0, $product->stock);
        $this->assertDatabaseMissing('inventory_management', ['product_id' => $product->id]);
    }

    public function test_updating_product_does_not_change_stock(): void
    {
        $token = $this->token();
        $product = $this->makeProduct(40);
        $auth = ['Authorization' => "Bearer {$token}"];

        $this->putJson("/api/products/{$product->id}", [
            'internal_code' => $product->internal_code,
            'name' => 'Nombre editado',
            'stock' => 999, // must be ignored
            'minimum_quantity' => 5,
            'on_promotion' => false,
            'unit_price' => 12,
            'wholesale_unit_price' => 10,
            'wholesale_min_quantity' => 10,
            'discount' => 0,
            'unit_id' => $product->unit_id,
            'product_category_id' => $product->product_category_id,
        ], $auth)->assertOk();

        $this->assertEquals(40, $product->fresh()->stock);
        $this->assertEquals('Nombre editado', $product->fresh()->name);
    }

    public function test_kardex_returns_running_balance(): void
    {
        $token = $this->token();
        $product = $this->makeProduct(100);
        $auth = ['Authorization' => "Bearer {$token}"];

        $this->postJson('/api/inventory-movements', [
            'product_id' => $product->id, 'movement_type' => 'inbound', 'quantity' => 10,
        ], $auth);

        $this->getJson("/api/products/{$product->id}/kardex", $auth)
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'product' => ['id', 'internal_code', 'name', 'stock', 'minimum_quantity', 'unit'],
                    'movements' => [['id', 'movement_type', 'quantity', 'stock_before', 'stock_after', 'status']],
                ],
            ]);
    }
}
