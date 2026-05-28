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
        Schema::create('xray_fallbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_id')->constrained('xray_inbounds')->onDelete('cascade');
            $table->string('path')->nullable();
            $table->string('alpn')->nullable();
            $table->string('dest_type')->default('port'); // port, unix, remote
            $table->string('dest_value'); // e.g., 80 or /tmp/nginx.sock
            $table->integer('xver')->default(0);
            $table->timestamps();
        });

        Schema::create('xray_balancers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('nodes')->onDelete('cascade');
            $table->string('tag')->unique();
            $table->text('selector'); // CSV of outbound tags
            $table->string('strategy')->default('random'); // random, leastPing, roundRobin
            $table->timestamps();
        });

        Schema::create('xray_routing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('nodes')->onDelete('cascade');
            $table->integer('priority')->default(0);
            $table->string('type')->default('field');
            $table->text('inbound_tags')->nullable(); // CSV
            $table->string('outbound_tag');
            $table->string('domain_strategy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xray_routing_rules');
        Schema::dropIfExists('xray_balancers');
        Schema::dropIfExists('xray_fallbacks');
    }
};
