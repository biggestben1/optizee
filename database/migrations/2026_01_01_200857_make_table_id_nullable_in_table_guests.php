<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support dropping foreign keys, so we need to recreate the table
            // Rename old table
            DB::statement('ALTER TABLE table_guests RENAME TO table_guests_old');
            
            // Create new table with nullable table_id
            Schema::create('table_guests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('table_id')->nullable()->constrained('tables')->onDelete('set null');
                $table->string('guest_name');
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('seated_at')->useCurrent();
                $table->timestamp('left_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            
            // Copy data from old table
            DB::statement('INSERT INTO table_guests SELECT * FROM table_guests_old');
            
            // Drop old table
            Schema::drop('table_guests_old');
        } else {
            // For other databases (MySQL, PostgreSQL, etc.)
            Schema::table('table_guests', function (Blueprint $table) {
                // Drop the foreign key constraint first
                $table->dropForeign(['table_id']);
            });
            
            Schema::table('table_guests', function (Blueprint $table) {
                // Make table_id nullable
                $table->foreignId('table_id')->nullable()->change();
            });
            
            Schema::table('table_guests', function (Blueprint $table) {
                // Re-add foreign key with nullOnDelete
                $table->foreign('table_id')->references('id')->on('tables')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite: recreate table with required table_id
            DB::statement('ALTER TABLE table_guests RENAME TO table_guests_old');
            
            Schema::create('table_guests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('table_id')->constrained('tables')->cascadeOnDelete();
                $table->string('guest_name');
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('seated_at')->useCurrent();
                $table->timestamp('left_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            
            // Copy data (only rows with table_id)
            DB::statement('INSERT INTO table_guests SELECT * FROM table_guests_old WHERE table_id IS NOT NULL');
            
            Schema::drop('table_guests_old');
        } else {
            // For other databases
            Schema::table('table_guests', function (Blueprint $table) {
                $table->dropForeign(['table_id']);
            });
            
            Schema::table('table_guests', function (Blueprint $table) {
                $table->foreignId('table_id')->nullable(false)->change();
            });
            
            Schema::table('table_guests', function (Blueprint $table) {
                $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            });
        }
    }
};
