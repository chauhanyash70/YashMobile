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
        Schema::create('mobiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->foreignId('model_id')->constrained();

            $table->string('hsn_number', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('storage', 50)->nullable();
            $table->string('ram', 50)->nullable();
            $table->string('battery_health', 20)->nullable();

            $table->enum('condition_type', ['new', 'used', 'refurbished'])->default('used');
            $table->enum('status', ['in_stock', 'sold', 'repair', 'returned', 'dead'])->default('in_stock');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobiles');
    }
};
