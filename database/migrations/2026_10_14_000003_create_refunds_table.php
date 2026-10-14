<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique();
            $table->date('refund_date');
            $table->string('area', 20); // rooms, hall, bar, kitchen, other
            $table->string('reference')->nullable(); // booking / invoice number being refunded
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 20)->default('cash'); // cash, transfer, pos
            $table->text('reason');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['refund_date', 'area']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
