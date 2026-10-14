<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            $table->decimal('cash_amount', 12, 2)->default(0)->after('change');
            $table->decimal('transfer_amount', 12, 2)->default(0)->after('cash_amount');
            $table->decimal('pos_amount', 12, 2)->default(0)->after('transfer_amount');
        });

        foreach (['cash', 'transfer', 'pos'] as $method) {
            DB::table('room_bookings')
                ->where('payment_method', $method)
                ->update(["{$method}_amount" => DB::raw('CASE WHEN amount_paid > `change` THEN amount_paid - `change` ELSE 0 END')]);
        }
    }

    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            $table->dropColumn(['cash_amount', 'transfer_amount', 'pos_amount']);
        });
    }
};
