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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('invoice_no', 100);
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('invoice_type', ['buy', 'sell', 'repair']);
            
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);

            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'upi', 'bank_transfer', 'card', 'credit', 'bajaj_finance'])->default('cash');
            $table->string('bajaj_approval_number')->nullable();

            $table->dateTime('invoice_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['invoice_no', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
