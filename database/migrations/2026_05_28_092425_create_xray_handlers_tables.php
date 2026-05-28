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
        Schema::create('xray_sniffing_configs', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('dest_override')->default('http,tls');
            $table->boolean('route_only')->default(false);
            $table->boolean('metadata_only')->default(false);
            $table->timestamps();
        });

        Schema::create('xray_policy_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('idn_nodes')->onDelete('cascade');
            $table->integer('level_id')->default(0);
            $table->integer('handshake')->default(4);
            $table->integer('conn_idle')->default(300);
            $table->integer('buffer_size')->default(512);
            $table->unique(['node_id', 'level_id']);
            $table->timestamps();
        });

        Schema::create('xray_inbounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_port_id')->constrained('physical_ports')->onDelete('cascade');
            $table->string('tag')->unique();
            $table->foreignId('sniffing_id')->nullable()->constrained('xray_sniffing_configs')->onDelete('set null');
            $table->foreignId('policy_level_id')->nullable()->constrained('xray_policy_levels')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('xray_outbounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('idn_nodes')->onDelete('cascade');
            $table->string('tag')->unique();
            $table->string('send_through')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xray_outbounds');
        Schema::dropIfExists('xray_inbounds');
        Schema::dropIfExists('xray_policy_levels');
        Schema::dropIfExists('xray_sniffing_configs');
    }
};
