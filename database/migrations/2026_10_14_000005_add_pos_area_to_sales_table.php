<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sales', 'pos_area')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->string('pos_area', 20)->nullable()->after('table_guest_id'); // bar, kitchen
            $table->index('pos_area');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['pos_area']);
            $table->dropColumn('pos_area');
        });
    }
};
