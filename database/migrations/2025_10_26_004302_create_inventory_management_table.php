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
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 12, 2)->unsigned();
            $table->string('motivo')->nullable();
            $table->decimal('stock_antes', 12, 2);
            $table->decimal('stock_despues', 12, 2);
            $table->timestamp('fecha_movimiento')->useCurrent();
            $table->string('estado_registro', 20)->default('activo');
            $table->timestamps();

            $table->index(['product_id', 'fecha_movimiento']);
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
