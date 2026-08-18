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
            $table->string('guest_name')->nullable()->after('customer_id');
            $table->string('guest_phone')->nullable()->after('guest_name');
            $table->string('guest_email')->nullable()->after('guest_phone');
            $table->string('guest_id_upload')->nullable()->after('guest_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_phone', 'guest_email', 'guest_id_upload']);
        });
    }
};
