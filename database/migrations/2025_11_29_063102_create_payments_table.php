<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->enum('payment_for', ['invoice','purchase','exchange']);
            $table->unsignedBigInteger('reference_id');
            $table->decimal('amount', 12, 2);
            $table->string('method')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('payments'); }
}
