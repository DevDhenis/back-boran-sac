<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_management', function (Blueprint $table) {
            // Why the movement happened (direction stays in movement_type).
            $table->enum('origin', ['manual', 'sale', 'sale_cancellation', 'customer_return'])
                ->default('manual')
                ->after('movement_type');
            // Link to the sale that originated the movement (sales/cancellations/returns).
            $table->foreignId('sale_id')->nullable()->after('product_id')
                ->constrained('sales')->nullOnDelete();
        });

        // Legacy manual "Devolución" movements become inbound + customer_return origin.
        DB::table('inventory_management')
            ->where('movement_type', 'return')
            ->update(['movement_type' => 'inbound', 'origin' => 'customer_return']);

        Schema::table('inventory_management', function (Blueprint $table) {
            $table->enum('movement_type', ['inbound', 'outbound', 'adjustment'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_management', function (Blueprint $table) {
            $table->enum('movement_type', ['inbound', 'outbound', 'adjustment', 'return'])->change();
        });

        Schema::table('inventory_management', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sale_id');
            $table->dropColumn('origin');
        });
    }
};
