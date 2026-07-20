<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->enum('status', ['requested', 'approved', 'rejected'])->default('requested');
            $table->text('reason');                       // motivo del cliente
            $table->foreignId('reviewed_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('review_note')->nullable();       // nota del staff al aprobar/rechazar
            $table->enum('refund_status', ['pending', 'refunded'])->default('pending');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained('sale_returns')->cascadeOnDelete();
            $table->foreignId('sales_item_id')->constrained('sales_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->timestamps();
        });

        // Link the inbound movement generated when a return is approved to its return.
        Schema::table('inventory_management', function (Blueprint $table) {
            $table->foreignId('sale_return_id')->nullable()->after('sale_id')
                ->constrained('sale_returns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_management', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sale_return_id');
        });
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
    }
};
