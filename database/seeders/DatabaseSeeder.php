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
        $this->call(PersonSeeder::class);         // personas: 1 admin + 1 cliente
        $this->call(UserSeeder::class);           // usuarios: 1 admin + 1 cliente
        $this->call(EmployeeSeeder::class);       // faceta empleado del admin (cuenta staff completa)
        $this->call(ClientSeeder::class);         // faceta cliente del cliente
        $this->call(AccessSeeder::class);         // accesos/permisos del sistema
        $this->call(AccessRoleSeeder::class);     // pivote rol↔acceso (admin=todo, cliente=público)

        // 🗂️ Catálogo base (datos de referencia, no transaccionales)
        $this->call(UnitSeeder::class);           // unidades de medida
        $this->call(ProductCategorySeeder::class); // categorías de productos

        // ⚠️ NO se siembran datos transaccionales ni de relleno (productos,
        //    inventario, ventas). Se cargan desde la app o corriendo manualmente
        //    sus seeders en local (p.ej. php artisan db:seed --class=ProductSeeder).
        //    Solo se crean 2 cuentas completas: admin y cliente.
    }
}
