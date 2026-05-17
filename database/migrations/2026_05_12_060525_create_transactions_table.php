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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('mobile_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('accessory_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');

            $table->enum('transaction_type', ['buy', 'sell', 'return', 'exchange', 'repair']);
            $table->decimal('price', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'upi', 'bank_transfer', 'card', 'credit', 'bajaj_finance'])->default('cash');
            $table->string('bajaj_approval_number')->nullable();

            $table->dateTime('transaction_date');
            $table->string('invoice_no', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
