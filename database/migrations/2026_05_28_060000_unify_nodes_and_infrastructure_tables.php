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
        Schema::create('idn_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hostname');
            $table->string('ip')->nullable(); // Tailscale IP
            $table->string('external_ip')->nullable();
            $table->integer('api_port')->default(10085);
            $table->string('role')->default('node'); // gateway, node, provider
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->string('os_type')->default('linux');
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('physical_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('idn_nodes')->onDelete('cascade');
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
        Schema::dropIfExists('idn_nodes');
    }
};
