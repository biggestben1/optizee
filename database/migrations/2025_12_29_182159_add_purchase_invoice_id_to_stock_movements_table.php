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
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'purchase_invoice_id')) {
                $table->unsignedBigInteger('purchase_invoice_id')->nullable()->after('supplier_supply_id');
                $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'purchase_invoice_id')) {
                // SQLite doesn't support dropping foreign keys, so we'll just drop the column
                if (config('database.default') !== 'sqlite') {
                    $table->dropForeign(['purchase_invoice_id']);
                }
                $table->dropColumn('purchase_invoice_id');
            }
        });
    }
};
