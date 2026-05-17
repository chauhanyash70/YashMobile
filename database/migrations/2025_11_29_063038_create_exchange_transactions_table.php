<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('exchange_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('old_device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('new_device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->decimal('old_device_value', 12, 2)->default(0);
            $table->decimal('new_device_price', 12, 2)->default(0);
            $table->decimal('difference_amount', 12, 2)->default(0); // amount customer pays
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('exchange_transactions'); }
}
