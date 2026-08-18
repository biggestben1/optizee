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
        Schema::table('room_bookings', function (Blueprint $table) {
            $table->enum('booking_type', ['overnight', 'short_stay'])->default('overnight')->after('booking_number');
            $table->time('check_in_time')->nullable()->after('check_in_date');
            $table->time('check_out_time')->nullable()->after('check_out_date');
            $table->integer('hours_stayed')->nullable()->after('number_of_nights');
            $table->decimal('hourly_rate', 12, 2)->nullable()->after('room_price_per_night');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_type', 'check_in_time', 'check_out_time', 'hours_stayed', 'hourly_rate']);
        });
    }
};
