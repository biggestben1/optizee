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
        Schema::table('room_categories', function (Blueprint $table) {
            $table->decimal('hourly_rate', 12, 2)->nullable()->after('price_per_room');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_categories', function (Blueprint $table) {
            $table->dropColumn('hourly_rate');
        });
    }
};
