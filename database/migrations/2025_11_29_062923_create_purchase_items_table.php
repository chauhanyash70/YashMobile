<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseItemsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('imei_id')->nullable()->constrained('device_imeis')->nullOnDelete();
            $table->enum('item_type', ['device', 'accessory']);
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12, 2);
            $table->decimal('repair_cost', 12, 2)->nullable();
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('purchase_items');
    }
}
