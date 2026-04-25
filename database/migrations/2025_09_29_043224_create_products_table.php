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
            $table->string('codigo_interno')->unique();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->decimal('stock', 10, 2)->default(0.000);
            $table->decimal('cantidad_minima', 10, 2)->default(0.000);
            $table->boolean('en_promocion')->default(false);
            $table->decimal('pre_uni', 10, 2);
            $table->decimal('pre_uni_may', 10, 2);
            $table->decimal('can_min_may', 10, 2);
            $table->integer('descuento')->default(0);
            $table->decimal('pre_fin', 10, 2)->default(0.00);
            $table->string('imagen')->nullable();
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('product_category_id')->constrained('product_categories');
            $table->char('estado_registro', 1)->default('A');
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
