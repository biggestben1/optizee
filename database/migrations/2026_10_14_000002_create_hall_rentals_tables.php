<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hall_occasions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hall_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('hall_occasion_id')->nullable()->constrained()->nullOnDelete();
            $table->string('occasion_name');
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->string('customer_email')->nullable();
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('expected_guests')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('change', 12, 2)->default(0);
            $table->decimal('cash_amount', 12, 2)->default(0);
            $table->decimal('transfer_amount', 12, 2)->default(0);
            $table->decimal('pos_amount', 12, 2)->default(0);
            $table->string('payment_method', 20)->default('cash');
            $table->string('status', 20)->default('confirmed');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index('event_date');
        });

        $now = now();
        DB::table('hall_occasions')->insert([
            ['name' => 'Birthday Party', 'price' => 300000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Conference Hall', 'price' => 250000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Wedding', 'price' => 300000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hall_bookings');
        Schema::dropIfExists('hall_occasions');
    }
};
