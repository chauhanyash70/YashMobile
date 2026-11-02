<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CommonDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:common-data 
                            {--dry-run : Preview changes without executing updates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely configure common table schemas (payment_method, finance_company_id, approval_number) and sync data across invoices and transactions.';

    /**
     * Target tables to apply common schema and data sync.
     *
     * @var array
     */
    protected $targetTables = ['invoices', 'transactions'];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info("==================================================");
        $this->info("          YASH MOBILE - COMMON DATA COMMAND       ");
        $this->info("==================================================");

        if ($dryRun) {
            $this->warn(">>> DRY-RUN MODE: No database modifications will be performed.");
        }

        // 1. Process finance_companies table (convert 'is_active' to 'status' ENUM)
        $this->processFinanceCompaniesTable($dryRun);

        // 2. Loop through each target table (invoices, transactions, etc.)
        foreach ($this->targetTables as $tableName) {
            $this->processTable($tableName, $dryRun);
        }

        $this->newLine();
        $this->info("✓ Common data configuration and sync completed successfully!");
        return Command::SUCCESS;
    }

    /**
     * Ensure finance_companies table has 'status' ENUM('active', 'inactive').
     */
    protected function processFinanceCompaniesTable(bool $dryRun): void
    {
        $this->newLine();
        $this->line("<fg=cyan>[*]</> Checking <fg=yellow>finance_companies</> table schema...");

        if (!Schema::hasTable('finance_companies')) {
            $this->warn("  ! Table 'finance_companies' does not exist yet. Run 'php artisan migrate' first.");
            return;
        }

        if (Schema::hasColumn('finance_companies', 'is_active') && !Schema::hasColumn('finance_companies', 'status')) {
            if ($dryRun) {
                $this->comment("  [DRY-RUN] Would rename 'is_active' to 'status' ENUM('active', 'inactive') in 'finance_companies'.");
            } else {
                DB::statement("ALTER TABLE `finance_companies` CHANGE COLUMN `is_active` `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
                DB::statement("UPDATE `finance_companies` SET `status` = 'active' WHERE `status` IS NULL OR `status` = '' OR `status` = '1'");
                $this->info("  ✓ Successfully converted 'is_active' to 'status' ENUM('active', 'inactive').");
            }
        } elseif (!Schema::hasColumn('finance_companies', 'status')) {
            if ($dryRun) {
                $this->comment("  [DRY-RUN] Would add column 'status' ENUM('active', 'inactive') to 'finance_companies'.");
            } else {
                Schema::table('finance_companies', function (Blueprint $table) {
                    $table->enum('status', ['active', 'inactive'])->default('active');
                });
                $this->info("  ✓ Added 'status' ENUM('active', 'inactive') column to 'finance_companies'.");
            }
        } else {
            $this->info("  ✓ Column 'status' ENUM already exists in 'finance_companies'.");
        }
    }

    /**
     * Process schema adjustment and data synchronization for a table.
     */
    protected function processTable(string $tableName, bool $dryRun): void
    {
        $this->newLine();
        $this->line("<fg=cyan>[*]</> Processing table: <fg=yellow>{$tableName}</>");

        if (!Schema::hasTable($tableName)) {
            $this->warn("  ! Table '{$tableName}' does not exist. Skipping.");
            return;
        }

        // A. Modify payment_method column from ENUM to VARCHAR(50)
        if ($dryRun) {
            $this->comment("  [DRY-RUN] Would alter 'payment_method' to VARCHAR(50) in '{$tableName}'.");
        } else {
            DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cash'");
            $this->info("  ✓ Ensured 'payment_method' is VARCHAR(50) to support dynamic payment methods.");
        }

        // B. Add finance_company_id and approval_number columns if missing
        if (!Schema::hasColumn($tableName, 'finance_company_id')) {
            if ($dryRun) {
                $this->comment("  [DRY-RUN] Would add column 'finance_company_id' to '{$tableName}'.");
            } else {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('finance_company_id')->nullable()->after('payment_method');
                });
                $this->info("  ✓ Added column 'finance_company_id'.");
            }
        } else {
            $this->info("  ✓ Column 'finance_company_id' already exists.");
        }

        if (!Schema::hasColumn($tableName, 'approval_number')) {
            if ($dryRun) {
                $this->comment("  [DRY-RUN] Would add column 'approval_number' to '{$tableName}'.");
            } else {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('approval_number', 100)->nullable()->after('bajaj_approval_number');
                });
                $this->info("  ✓ Added column 'approval_number'.");
            }
        } else {
            $this->info("  ✓ Column 'approval_number' already exists.");
        }

        // C. Sync existing data from bajaj_approval_number -> approval_number
        if (Schema::hasColumn($tableName, 'bajaj_approval_number')) {
            $pendingApprovalCount = DB::table($tableName)
                ->whereNotNull('bajaj_approval_number')
                ->where(function ($q) {
                    $q->whereNull('approval_number')->orWhere('approval_number', '');
                })
                ->count();

            if ($pendingApprovalCount > 0) {
                if ($dryRun) {
                    $this->comment("  [DRY-RUN] {$pendingApprovalCount} records with 'bajaj_approval_number' would be copied to 'approval_number'.");
                } else {
                    DB::statement("UPDATE `{$tableName}` SET `approval_number` = `bajaj_approval_number` WHERE `bajaj_approval_number` IS NOT NULL AND (`approval_number` IS NULL OR `approval_number` = '')");
                    $this->info("  ✓ Synced {$pendingApprovalCount} approval number(s) into 'approval_number'.");
                }
            } else {
                $this->info("  ✓ All existing approval numbers are already synced.");
            }
        }

        // D. Link existing 'bajaj_finance' records to finance_company_id = 1
        $pendingCompanyCount = DB::table($tableName)
            ->where('payment_method', 'bajaj_finance')
            ->whereNull('finance_company_id')
            ->count();

        if ($pendingCompanyCount > 0) {
            if ($dryRun) {
                $this->comment("  [DRY-RUN] {$pendingCompanyCount} 'bajaj_finance' records would be linked to finance_company_id = 1.");
            } else {
                DB::statement("UPDATE `{$tableName}` SET `finance_company_id` = 1 WHERE `payment_method` = 'bajaj_finance' AND `finance_company_id` IS NULL");
                $this->info("  ✓ Linked {$pendingCompanyCount} existing record(s) to Bajaj Finance (ID: 1).");
            }
        } else {
            $this->info("  ✓ All existing 'bajaj_finance' records already linked to finance_company_id.");
        }
    }
}

