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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->date('expense_date');
            $table->string('category'); // bar, hotel, electricity, water, internet, maintenance, supplies, other
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, transfer, pos
            $table->string('vendor')->nullable(); // Who was paid
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Who recorded the expense
            $table->string('receipt_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
