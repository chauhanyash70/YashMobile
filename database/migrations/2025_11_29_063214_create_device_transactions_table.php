<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('device_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['purchase','sale','repair','exchange_in','exchange_out']);
            $table->foreignId('related_id')->nullable(); // e.g. purchase_id/invoice_id/exchange_id
            $table->decimal('amount', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('device_transactions'); }
}
