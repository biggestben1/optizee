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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // Furniture, Equipment, Vehicle, etc.
            $table->decimal('value', 15, 2);
            $table->date('purchase_date');
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, disposed, repair
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
