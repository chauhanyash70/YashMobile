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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug')->nullable();
            $table->enum('type', ['device', 'accessory', 'both'])->default('device');
            $table->timestamps();

            $table->unique(['slug', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
