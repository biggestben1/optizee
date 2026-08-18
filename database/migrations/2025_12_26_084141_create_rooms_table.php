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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_category_id')->constrained()->cascadeOnDelete();
            $table->string('room_number')->unique(); // e.g., "101", "201", "Suite-1"
            $table->string('room_name')->nullable(); // For suite sub-rooms: "Master Bed 1", "Deluxe", etc.
            $table->decimal('price_per_night', 12, 2)->nullable(); // Override category price for suite sub-rooms
            $table->enum('status', ['available', 'booked', 'checked-in', 'checked-out', 'cleaning', 'maintenance'])->default('available');
            $table->boolean('is_suite_sub_room')->default(false); // Whether this is a sub-room within a suite
            $table->foreignId('parent_suite_id')->nullable()->constrained('rooms')->nullOnDelete(); // Link to parent suite room
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
