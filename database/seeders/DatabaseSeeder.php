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
        $this->call(ClientSeeder::class);         // faceta cliente del cliente
        $this->call(AccessSeeder::class);         // accesos/permisos del sistema
        $this->call(AccessRoleSeeder::class);     // pivote rol↔acceso (admin=todo, cliente=público)

        // 🗂️ Catálogo base (datos de referencia, no transaccionales)
        $this->call(UnitSeeder::class);           // unidades de medida
        $this->call(ProductCategorySeeder::class); // categorías de productos

        // ⚠️ En PRODUCCIÓN no se siembran datos transaccionales ni de relleno:
        //    solo lo de arriba (base + 2 cuentas). El admin NO es colaborador.
        //
        // 🧪 Solo en LOCAL: para que `migrate:fresh --seed` deje una BD completa y
        //    coherente para el demo, se encadenan los seeders de relleno.
        //    DemoDataSeeder crea productos y, a su vez, el kardex coherente
        //    (InventoryMovementDemoSeeder); DashboardDemoSeeder crea ventas/pagos.
        //    El guard de entorno evita que estos datos lleguen a Aiven/Render.
        if (app()->environment('local')) {
            $this->call(DemoDataSeeder::class);       // colaboradores + productos + inventario (kardex)
            $this->call(DashboardDemoSeeder::class);  // ventas + pagos (para el Panel)
        }
    }
}
