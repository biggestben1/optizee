<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // manager, supervisor, cashier, storekeeper
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Add role_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('is_admin')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'is_active']);
        });
        Schema::dropIfExists('roles');
    }
};











