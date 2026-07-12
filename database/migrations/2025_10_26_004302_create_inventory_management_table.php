<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_management', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('movement_type', ['inbound', 'outbound', 'adjustment']);
            $table->decimal('quantity', 12, 2)->unsigned();
            $table->string('reason')->nullable();
            $table->decimal('stock_before', 12, 2);
            $table->decimal('stock_after', 12, 2);
            $table->timestamp('movement_date')->useCurrent();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['product_id', 'movement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_management');
    }
};
