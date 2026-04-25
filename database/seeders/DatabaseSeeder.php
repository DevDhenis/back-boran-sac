<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 📄 Datos base
        $this->call(DocumentTypeSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PersonSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ClientSeeder::class);
        $this->call(AccessSeeder::class);
        $this->call(AccessRoleSeeder::class);

        // 🛒 Módulo de carrito de compras
        $this->call(UnitSeeder::class);
        $this->call(ProductCategorySeeder::class);
        $this->call(ProductSeeder::class);

        // 📦 Inventario
        $this->call([
            ProductSeeder::class,
            InventoryManagementSeeder::class,
        ]);
    }
}
