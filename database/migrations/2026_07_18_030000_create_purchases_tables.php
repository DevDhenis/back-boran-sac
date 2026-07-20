<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('purchase_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('document_number')->nullable(); // nro de factura/guía del proveedor
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // Purchases (and later supplier returns) generate traceable movements.
        Schema::table('inventory_management', function (Blueprint $table) {
            $table->foreignId('purchase_id')->nullable()->after('sale_id')
                ->constrained('purchases')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('purchase_id')
                ->constrained('suppliers')->nullOnDelete();
        });

        Schema::table('inventory_management', function (Blueprint $table) {
            $table->enum('origin', ['manual', 'sale', 'sale_cancellation', 'customer_return', 'purchase', 'supplier_return'])
                ->default('manual')->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_management', function (Blueprint $table) {
            $table->enum('origin', ['manual', 'sale', 'sale_cancellation', 'customer_return'])
                ->default('manual')->change();
        });
        Schema::table('inventory_management', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
            $table->dropConstrainedForeignId('purchase_id');
        });
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
