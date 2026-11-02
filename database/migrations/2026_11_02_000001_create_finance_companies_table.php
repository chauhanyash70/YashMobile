<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create finance_companies table if not exists
        if (!Schema::hasTable('finance_companies')) {
            Schema::create('finance_companies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('name', 100);
                $table->string('code', 50)->unique();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });

            // Insert default finance companies
            $now = now();
            DB::table('finance_companies')->insert([
                ['id' => 1, 'name' => 'Bajaj Finance', 'code' => 'bajaj_finance', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
                ['id' => 2, 'name' => 'HDFC Bank', 'code' => 'hdfc', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
                ['id' => 3, 'name' => 'Home Credit', 'code' => 'home_credit', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
                ['id' => 4, 'name' => 'TVS Credit', 'code' => 'tvs_credit', 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_companies');
    }
};

