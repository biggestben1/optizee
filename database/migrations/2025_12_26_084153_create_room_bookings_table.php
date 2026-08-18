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
        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique(); // Invoice-like number
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Staff who made booking
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('number_of_nights');
            $table->decimal('room_price_per_night', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0); // VAT
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('change', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'transfer', 'pos', 'credit', 'mixed'])->default('cash');
            $table->enum('status', ['pending', 'confirmed', 'checked-in', 'checked-out', 'cancelled'])->default('confirmed');
            $table->boolean('is_credit_booking')->default(false);
            $table->boolean('is_full_suite_booking')->default(false); // For suite bookings
            $table->text('notes')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_bookings');
    }
};
