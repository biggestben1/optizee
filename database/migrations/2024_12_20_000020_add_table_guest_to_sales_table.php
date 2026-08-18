<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('table_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->foreignId('table_guest_id')->nullable()->after('table_id')->constrained('table_guests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropForeign(['table_guest_id']);
            $table->dropColumn(['table_id', 'table_guest_id']);
        });
    }
};









