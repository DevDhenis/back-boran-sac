<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('return_date');
            $table->text('reason');
            $table->enum('credit_status', ['pending', 'credited'])->default('pending'); // nota de crédito del proveedor
            $table->timestamps();
        });

        Schema::create('supplier_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_return_id')->constrained('supplier_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->timestamps();
        });

        Schema::table('inventory_management', function (Blueprint $table) {
            $table->foreignId('supplier_return_id')->nullable()->after('supplier_id')
                ->constrained('supplier_returns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_management', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_return_id');
        });
        Schema::dropIfExists('supplier_return_items');
        Schema::dropIfExists('supplier_returns');
    }
};
