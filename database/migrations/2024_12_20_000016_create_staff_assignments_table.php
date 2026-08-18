<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->enum('shift_type', ['morning', 'afternoon', 'evening', 'night', 'full_day', 'custom'])->default('full_day');
            $table->time('shift_start')->nullable();
            $table->time('shift_end')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null means ongoing
            $table->json('working_days')->nullable(); // e.g., ["monday", "tuesday", "wednesday"]
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Prevent duplicate active assignments for same user in same section
            $table->index(['user_id', 'section_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_assignments');
    }
};










