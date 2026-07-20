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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('internal_code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('stock', 10, 2)->default(0.000);
            $table->decimal('minimum_quantity', 10, 2)->default(0.000);
            $table->boolean('on_promotion')->default(false);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('wholesale_unit_price', 10, 2);
            $table->decimal('wholesale_min_quantity', 10, 2);
            $table->integer('discount')->default(0);
            $table->decimal('final_price', 10, 2)->default(0.00);
            $table->string('image')->nullable();
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('product_category_id')->constrained('product_categories');
            $table->char('status', 1)->default('A');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
