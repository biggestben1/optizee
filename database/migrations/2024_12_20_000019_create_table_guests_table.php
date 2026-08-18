<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name'); // e.g., "John", "Guest 1", "Friend 1"
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // If linked to customer account
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('seated_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_guests');
    }
};









