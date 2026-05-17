<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhoneModelsTable extends Migration
{
    public function up()
    {
        Schema::create('phone_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('phone_models'); }
}
