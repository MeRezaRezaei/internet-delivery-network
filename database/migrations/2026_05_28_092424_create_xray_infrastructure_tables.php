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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('hostname')->unique();
            $table->string('internal_ip')->unique();
            $table->string('external_ip')->nullable();
            $table->string('os_type')->default('linux');
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamps();
        });

        Schema::create('physical_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('nodes')->onDelete('cascade');
            $table->integer('port_number');
            $table->enum('protocol', ['tcp', 'udp'])->default('tcp');
            $table->enum('status', ['listening', 'reserved', 'free'])->default('free');
            $table->unique(['node_id', 'port_number', 'protocol']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_ports');
        Schema::dropIfExists('nodes');
    }
};
