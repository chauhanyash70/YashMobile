<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinanceCompany;

class FinanceCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'Bajaj Finance', 'code' => 'bajaj_finance', 'status' => 'active'],
            ['name' => 'HDFC Bank', 'code' => 'hdfc', 'status' => 'active'],
            ['name' => 'Home Credit', 'code' => 'home_credit', 'status' => 'active'],
            ['name' => 'TVS Credit', 'code' => 'tvs_credit', 'status' => 'active'],
        ];

        foreach ($companies as $company) {
            FinanceCompany::updateOrCreate(
                ['code' => $company['code']],
                [
                    'name' => $company['name'],
                    'status' => $company['status'],
                ]
            );
        }
    }
}

