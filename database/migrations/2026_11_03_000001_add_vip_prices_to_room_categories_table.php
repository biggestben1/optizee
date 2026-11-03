<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('room_categories', 'vip_price_per_room')) {
            return;
        }

        Schema::table('room_categories', function (Blueprint $table) {
            $after = Schema::hasColumn('room_categories', 'online_hourly_rate')
                ? 'online_hourly_rate'
                : 'hourly_rate';
            $table->decimal('vip_price_per_room', 12, 2)->nullable()->after($after);
            $table->decimal('vip_hourly_rate', 12, 2)->nullable()->after('vip_price_per_room');
        });
    }

    public function down(): void
    {
        Schema::table('room_categories', function (Blueprint $table) {
            $table->dropColumn(['vip_price_per_room', 'vip_hourly_rate']);
        });
    }
};
