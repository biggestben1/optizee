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
        Schema::create('owner_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->date('purchase_date');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Who recorded the purchase
            $table->text('description');
            $table->text('items_description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->string('vendor')->nullable(); // Where the purchase was made
            $table->enum('payment_method', ['cash', 'transfer', 'pos', 'credit'])->default('cash');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_purchases');
    }
};
