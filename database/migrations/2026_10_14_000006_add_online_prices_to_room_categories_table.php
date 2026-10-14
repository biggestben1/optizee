<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('room_categories', 'online_price_per_room')) {
            return;
        }

        Schema::table('room_categories', function (Blueprint $table) {
            $table->decimal('online_price_per_room', 12, 2)->nullable()->after('hourly_rate');
            $table->decimal('online_hourly_rate', 12, 2)->nullable()->after('online_price_per_room');
        });
    }

    public function down(): void
    {
        Schema::table('room_categories', function (Blueprint $table) {
            $table->dropColumn(['online_price_per_room', 'online_hourly_rate']);
        });
    }
};
