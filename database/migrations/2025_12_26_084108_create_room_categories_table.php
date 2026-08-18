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
        Schema::create('room_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Standard, Semi-Executive, Executive, Suite
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_per_room', 12, 2)->nullable(); // Price for regular categories
            $table->decimal('full_suite_price', 12, 2)->nullable(); // Price for full suite booking (Suite category only)
            $table->integer('total_rooms')->default(0); // Total number of rooms in this category
            $table->boolean('is_suite')->default(false); // Whether this is a suite category
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_categories');
    }
};
