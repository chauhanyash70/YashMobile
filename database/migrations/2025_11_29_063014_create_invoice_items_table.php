<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('imei_id')->nullable()->constrained('device_imeis')->nullOnDelete();
            $table->enum('item_type', ['device', 'accessory']);
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
}
