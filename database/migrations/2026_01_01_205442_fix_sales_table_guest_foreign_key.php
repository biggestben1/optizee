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
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support dropping foreign keys directly
            // We need to recreate the sales table with the correct foreign key reference
            
            // First, check if table_guests_old exists and drop it
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='table_guests_old'");
            if (!empty($tables)) {
                DB::statement('DROP TABLE IF EXISTS table_guests_old');
            }
            
            // Disable foreign key checks temporarily
            DB::statement('PRAGMA foreign_keys=off;');
            
            // Get the current schema of sales table
            $columns = DB::select("PRAGMA table_info(sales)");
            
            // Create new sales table with correct foreign key
            DB::statement("
                CREATE TABLE sales_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    invoice_number VARCHAR(255) UNIQUE NOT NULL,
                    user_id INTEGER NOT NULL,
                    shift_id INTEGER,
                    customer_id INTEGER,
                    table_id INTEGER,
                    table_guest_id INTEGER,
                    subtotal DECIMAL(12,2) NOT NULL,
                    discount DECIMAL(12,2) DEFAULT 0,
                    tax DECIMAL(12,2) DEFAULT 0,
                    total DECIMAL(12,2) NOT NULL,
                    amount_paid DECIMAL(12,2) DEFAULT 0,
                    change DECIMAL(12,2) DEFAULT 0,
                    payment_method VARCHAR(255) DEFAULT 'cash',
                    status VARCHAR(255) DEFAULT 'completed',
                    is_credit_sale BOOLEAN DEFAULT 0,
                    notes TEXT,
                    voided_by INTEGER,
                    voided_at TIMESTAMP,
                    void_reason TEXT,
                    kitchen_status VARCHAR(255),
                    kitchen_ready_at TIMESTAMP,
                    prepared_by INTEGER,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE SET NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
                    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
                    FOREIGN KEY (table_guest_id) REFERENCES table_guests(id) ON DELETE SET NULL,
                    FOREIGN KEY (voided_by) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (prepared_by) REFERENCES users(id) ON DELETE SET NULL
                )
            ");
            
            // Copy all data from old table to new table
            DB::statement("INSERT INTO sales_new SELECT * FROM sales");
            
            // Drop old table
            DB::statement('DROP TABLE sales');
            
            // Rename new table
            DB::statement('ALTER TABLE sales_new RENAME TO sales');
            
            // Re-enable foreign key checks
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // For MySQL/PostgreSQL, we can drop and recreate the foreign key
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['table_guest_id']);
            });
            
            Schema::table('sales', function (Blueprint $table) {
                $table->foreign('table_guest_id')->references('id')->on('table_guests')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration fixes a broken state, so we don't need to reverse it
        // The foreign key should always point to table_guests, not table_guests_old
    }
};
