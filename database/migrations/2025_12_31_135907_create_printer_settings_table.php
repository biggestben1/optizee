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
        Schema::create('printer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default Printer');
            $table->enum('type', ['usb', 'network', 'bluetooth', 'browser'])->default('browser');
            $table->string('connection_type')->default('browser'); // browser, network_ip, usb_port, bluetooth_address
            $table->string('ip_address')->nullable(); // For network printers
            $table->integer('port')->default(9100); // Default ESC/POS port
            $table->string('usb_port')->nullable(); // For USB printers
            $table->integer('paper_width')->default(80); // Paper width in mm (58mm, 80mm)
            $table->string('encoding')->default('utf-8');
            $table->boolean('auto_cut')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_settings');
    }
};
