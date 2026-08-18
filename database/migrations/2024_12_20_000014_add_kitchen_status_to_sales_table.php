<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('kitchen_status', ['pending', 'preparing', 'ready', 'served'])->default('pending')->after('status');
            $table->timestamp('kitchen_ready_at')->nullable()->after('kitchen_status');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete()->after('kitchen_ready_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['prepared_by']);
            $table->dropColumn(['kitchen_status', 'kitchen_ready_at', 'prepared_by']);
        });
    }
};










