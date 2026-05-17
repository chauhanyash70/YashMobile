<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('model_id')->constrained('phone_models')->cascadeOnDelete();
            $table->string('storage')->nullable();
            $table->string('ram')->nullable();
            $table->string('color')->nullable();
            $table->enum('condition', ['new', 'old'])->default('new');
            $table->enum('status', ['in_stock', 'sold', 'reserved', 'exchanged', 'returned'])->default('in_stock');
            $table->decimal('buy_price', 12, 2)->nullable();
            $table->decimal('sell_price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->date('purchase_date')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
