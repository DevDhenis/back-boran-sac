<?php

namespace Tests\Feature;

use App\Models\Access;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseSupplierReturnTest extends TestCase
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

    private function makeProduct(float $stock = 0): Product
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

    public function test_purchases_require_compras_access(): void
    {
        $token = $this->token();
        $this->getJson('/api/purchases', ['Authorization' => "Bearer {$token}"])->assertStatus(403);
    }

    public function test_purchase_increases_stock_and_creates_movement(): void
    {
        $token = $this->token(['Compras']);
        $supplier = Supplier::create(['name' => 'Prov X', 'status' => 'A']);
        $product = $this->makeProduct(0);

        $this->postJson('/api/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 20, 'unit_cost' => 5]],
        ], ['Authorization' => "Bearer {$token}"])->assertCreated();

        $this->assertEquals(20, $product->fresh()->stock);
        $this->assertDatabaseHas('inventory_management', [
            'product_id' => $product->id, 'origin' => 'purchase', 'movement_type' => 'inbound', 'supplier_id' => $supplier->id,
        ]);
    }

    public function test_supplier_return_decreases_stock(): void
    {
        $token = $this->token(['Compras']);
        $auth = ['Authorization' => "Bearer {$token}"];
        $supplier = Supplier::create(['name' => 'Prov Y', 'status' => 'A']);
        $product = $this->makeProduct(0);

        // Buy 20 first.
        $this->postJson('/api/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 20, 'unit_cost' => 5]],
        ], $auth)->assertCreated();

        // Return 5 to the supplier.
        $this->postJson('/api/supplier-returns', [
            'supplier_id' => $supplier->id,
            'reason' => 'Llegaron fallados',
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ], $auth)->assertCreated();

        $this->assertEquals(15, $product->fresh()->stock);
        $this->assertDatabaseHas('inventory_management', [
            'product_id' => $product->id, 'origin' => 'supplier_return', 'movement_type' => 'outbound',
        ]);
    }

    public function test_cannot_return_more_than_purchased_to_supplier(): void
    {
        $token = $this->token(['Compras']);
        $auth = ['Authorization' => "Bearer {$token}"];
        $supplier = Supplier::create(['name' => 'Prov Z', 'status' => 'A']);
        $product = $this->makeProduct(0);

        $this->postJson('/api/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 5]],
        ], $auth)->assertCreated();

        // Bought 10, try to return 30 -> rejected.
        $this->postJson('/api/supplier-returns', [
            'supplier_id' => $supplier->id, 'reason' => 'x',
            'items' => [['product_id' => $product->id, 'quantity' => 30]],
        ], $auth)->assertStatus(422);
    }
}
