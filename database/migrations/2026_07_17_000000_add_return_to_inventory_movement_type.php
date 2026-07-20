<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Laravel's schema builder applies this per driver: MODIFY on MySQL and a
        // table rebuild on sqlite (where the enum is enforced via a CHECK constraint).
        Schema::table('inventory_management', function (Blueprint $table) {
            $table->enum('movement_type', ['inbound', 'outbound', 'adjustment', 'return'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_management', function (Blueprint $table) {
            $table->enum('movement_type', ['inbound', 'outbound', 'adjustment'])->change();
        });
    }
};
