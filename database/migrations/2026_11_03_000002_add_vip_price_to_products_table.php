<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'vip_price')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('vip_price', 12, 2)->nullable()->after('selling_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('vip_price');
        });
    }
};
