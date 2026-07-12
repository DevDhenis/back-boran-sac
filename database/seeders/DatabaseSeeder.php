<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 📄 Datos base indispensables para autenticación y autorización
        $this->call(DocumentTypeSeeder::class);   // tipos de documento (DNI, RUC, ...)
        $this->call(RoleSeeder::class);           // roles: Administrador General, Cliente
        $this->call(PersonSeeder::class);         // personas base (admin + clientes de prueba)
        $this->call(UserSeeder::class);           // usuario admin + clientes de prueba
        $this->call(ClientSeeder::class);         // faceta cliente de las personas de prueba
        $this->call(AccessSeeder::class);         // accesos/permisos del sistema
        $this->call(AccessRoleSeeder::class);     // pivote rol↔acceso (admin=todo, cliente=público)

        // 🗂️ Catálogo base (datos de referencia, no transaccionales)
        $this->call(UnitSeeder::class);           // unidades de medida
        $this->call(ProductCategorySeeder::class); // categorías de productos

        // ⚠️ NO se siembran datos transaccionales ni de relleno.
        //    Productos, inventario, empleados y ventas se cargan desde la app
        //    (o corriendo manualmente sus seeders en local, p.ej.:
        //     php artisan db:seed --class=ProductSeeder).
        //    EmployeeSeeder e InventoryManagementSeeder usan factories/Faker,
        //    que NO está disponible en el build de producción (composer --no-dev).
    }
}
