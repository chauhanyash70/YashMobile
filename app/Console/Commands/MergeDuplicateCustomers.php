<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeDuplicateCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merge-duplicate-customers 
                            {--dry-run : Run the merge logic in dry-run mode to see what would be merged without writing to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely merge duplicate customer records with similar phone numbers and transfer their invoices/transactions.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info("=== DRY-RUN MODE ACTIVE: No changes will be saved to the database ===");
        }

        // Get all customers
        $customers = Customer::all();

        // Group customers by their standardized phone numbers
        $groups = $customers->groupBy(function ($customer) {
            return Customer::standardizePhoneNumber($customer->phone);
        });

        $mergedCount = 0;
        $deletedCount = 0;

        foreach ($groups as $standardizedPhone => $group) {
            // Only process groups that have duplicates
            if ($group->count() <= 1) {
                continue;
            }

            $this->line("--------------------------------------------------");
            $this->info("Processing group for standardized phone: {$standardizedPhone}");

            // 1. Identify primary customer
            // Prefer:
            // - Starts with '+91'
            // - The oldest created_at (id-based)
            $primary = $group->first(function ($c) {
                return str_starts_with($c->phone, '+91');
            });

            if (!$primary) {
                $primary = $group->sortBy('id')->first();
            }

            $primaryId = $primary->id;
            $this->comment("Selected Primary Customer: [ID: {$primaryId}] Name: '{$primary->name}', Phone: '{$primary->phone}', Created: {$primary->created_at}");

            // 2. Identify duplicate customers
            $duplicates = $group->reject(function ($c) use ($primaryId) {
                return $c->id === $primaryId;
            });

            $duplicateIds = $duplicates->pluck('id')->toArray();

            foreach ($duplicates as $duplicate) {
                $invoiceCount = Invoice::where('customer_id', $duplicate->id)->count();
                $transactionCount = Transaction::where('customer_id', $duplicate->id)->count();

                $this->line("  - Duplicate found: [ID: {$duplicate->id}] Name: '{$duplicate->name}', Phone: '{$duplicate->phone}', Invoices: {$invoiceCount}, Transactions: {$transactionCount}");
            }

            // 3. Move invoices and transactions, and delete duplicates
            if (!$dryRun) {
                try {
                    DB::beginTransaction();

                    // Reassign Invoices
                    $updatedInvoices = Invoice::whereIn('customer_id', $duplicateIds)->update(['customer_id' => $primaryId]);
                    
                    // Reassign Transactions
                    $updatedTransactions = Transaction::whereIn('customer_id', $duplicateIds)->update(['customer_id' => $primaryId]);

                    // Delete duplicates
                    $deletedCustomers = Customer::whereIn('id', $duplicateIds)->delete();

                    DB::commit();

                    $this->info("  => Success: Reassigned {$updatedInvoices} invoices, {$updatedTransactions} transactions. Deleted {$deletedCustomers} duplicate customer(s).");
                    $mergedCount += $updatedInvoices;
                    $deletedCount += $deletedCustomers;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("  => Error merging group: " . $e->getMessage());
                }
            } else {
                // Dry run stats
                $totalDuplicateInvoices = Invoice::whereIn('customer_id', $duplicateIds)->count();
                $totalDuplicateTransactions = Transaction::whereIn('customer_id', $duplicateIds)->count();
                $this->info("  [Dry Run Preview] Would reassign {$totalDuplicateInvoices} invoices and {$totalDuplicateTransactions} transactions. Would delete " . count($duplicateIds) . " duplicate customer(s).");
            }
        }

        // Phase 2: Standardize phone numbers of all remaining customers in the database
        $this->line("");
        $this->info("=== Phase 2: Standardizing phone numbers of all remaining customers ===");
        $remainingCustomers = Customer::all();
        $standardizedCount = 0;
        foreach ($remainingCustomers as $customer) {
            $oldPhone = $customer->phone;
            $newPhone = Customer::standardizePhoneNumber($oldPhone);
            if ($oldPhone !== $newPhone) {
                if (!$dryRun) {
                    $customer->phone = $newPhone;
                    $customer->save();
                }
                $this->line("  Standardizing Customer ID {$customer->id} ('{$customer->name}'): '{$oldPhone}' => '{$newPhone}'");
                $standardizedCount++;
            }
        }

        $this->line("--------------------------------------------------");
        if (!$dryRun) {
            $this->info("Completed! Total duplicate customers deleted: {$deletedCount}. Invoices reassigned: {$mergedCount}. Standardized {$standardizedCount} customer phone numbers.");
        } else {
            $this->info("Dry run preview complete. Would standardize {$standardizedCount} customer phone numbers.");
        }
    }
}
