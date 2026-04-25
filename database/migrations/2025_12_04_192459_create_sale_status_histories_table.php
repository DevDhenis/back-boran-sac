<?php
// database/migrations/2025_12_04_000010_create_sale_status_histories_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sale_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->string('previous_status')->nullable();
            $table->string('new_status');
            $table->foreignId('changed_by_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('changed_by_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('sale_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('sale_status_histories');
    }
};
