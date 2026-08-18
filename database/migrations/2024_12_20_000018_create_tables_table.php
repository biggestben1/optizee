<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // e.g., "T1", "T2", "VIP-1"
            $table->string('name')->nullable(); // Optional name
            $table->integer('capacity')->default(4); // Number of seats
            $table->string('location')->nullable(); // e.g., "Indoor", "Outdoor", "VIP Section"
            $table->enum('status', ['available', 'occupied', 'reserved', 'cleaning'])->default('available');
            $table->foreignId('current_shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('served_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occupied_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};









