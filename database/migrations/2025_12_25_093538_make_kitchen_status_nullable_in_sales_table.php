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
        // For SQLite, we need to recreate the table to modify the column
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN for enum, so we'll use a workaround
            // We'll change the column to allow NULL by recreating the table
            DB::statement('PRAGMA foreign_keys=off;');
            
            // Create new table with nullable kitchen_status
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
            
            // Copy data
            DB::statement("
                INSERT INTO sales_new SELECT * FROM sales
            ");
            
            // Drop old table
            DB::statement('DROP TABLE sales;');
            
            // Rename new table
            DB::statement('ALTER TABLE sales_new RENAME TO sales;');
            
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // For MySQL/PostgreSQL, we can alter the column
            Schema::table('sales', function (Blueprint $table) {
                $table->enum('kitchen_status', ['pending', 'preparing', 'ready', 'served'])->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reversing this migration for SQLite is complex and may cause data loss
        // It's recommended to backup before running down()
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('sales', function (Blueprint $table) {
                $table->enum('kitchen_status', ['pending', 'preparing', 'ready', 'served'])->default('pending')->change();
            });
        }
    }
};
