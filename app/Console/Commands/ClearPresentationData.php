<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Table;
use App\Models\TableGuest;
use App\Models\User;
use Illuminate\Console\Command;

class ClearPresentationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presentation:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all tables, orders, and guests for presentation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing presentation data...');
        
        // Disable foreign key checks temporarily
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Clear sale items first (foreign key constraint)
            $saleItemsCount = SaleItem::count();
            SaleItem::truncate();
            $this->info("✓ Deleted {$saleItemsCount} sale items");
            
            // Clear sales/orders
            $salesCount = Sale::count();
            Sale::truncate();
            $this->info("✓ Deleted {$salesCount} sales/orders");
            
            // Clear table guests
            $guestsCount = TableGuest::count();
            TableGuest::truncate();
            $this->info("✓ Deleted {$guestsCount} table guests");
            
            // Clear tables
            $tablesCount = Table::count();
            Table::truncate();
            $this->info("✓ Deleted {$tablesCount} tables");
            
            // Delete all non-admin users
            $nonAdminUsers = User::where('is_admin', false)->get();
            $nonAdminCount = $nonAdminUsers->count();
            
            foreach ($nonAdminUsers as $user) {
                // Delete related data first to avoid foreign key issues
                $user->sales()->delete();
                $user->shifts()->delete();
                $user->customerPayments()->delete();
                $user->supplierPayments()->delete();
                $user->stockMovements()->delete();
                $user->auditLogs()->delete();
                $user->assignments()->delete();
                $user->delete();
            }
            
            $this->info("✓ Deleted {$nonAdminCount} non-admin users");
            
            // Show remaining admin user
            $adminUser = User::where('is_admin', true)->first();
            if ($adminUser) {
                $this->info("✓ Kept admin user: {$adminUser->name} ({$adminUser->email})");
            }
            
            $this->newLine();
            $this->info('✓ All presentation data cleared successfully!');
            $this->info('The database is now ready for a clean presentation.');
        } finally {
            // Re-enable foreign key checks
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        return Command::SUCCESS;
    }
}

