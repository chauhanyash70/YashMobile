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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('mobile_id')->constrained()->onDelete('cascade');
            $table->text('issue')->nullable();
            $table->decimal('repair_cost', 10, 2)->default(0);
            $table->string('technician_name', 150)->nullable();
            $table->enum('repair_status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->dateTime('repair_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
