<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceImeisTable extends Migration
{
    public function up()
    {
        Schema::create('device_imeis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('imei')->unique();
            $table->enum('status', ['available','sold','blocked','returned'])->default('available');
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('device_imeis'); }
}
