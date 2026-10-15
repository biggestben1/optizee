<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('purchase_requisitions', 'discount_received')) {
            return;
        }

        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->decimal('discount_received', 12, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropColumn('discount_received');
        });
    }
};
