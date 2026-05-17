<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessoriesTable extends Migration
{
    public function up()
    {
        Schema::create('accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->date('purchase_date')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('accessories'); }
}
