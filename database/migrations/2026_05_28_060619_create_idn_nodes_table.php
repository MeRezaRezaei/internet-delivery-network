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
        Schema::create('idn_nodes', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name')->unique();
            $blueprint->string('hostname');
            $blueprint->string('ip')->nullable();
            $blueprint->integer('api_port')->default(10085);
            $blueprint->string('role')->default('core_aggregator'); // edge_relay, core_aggregator, dns_resolver
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamp('last_heartbeat_at')->nullable();
            $blueprint->json('metadata')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idn_nodes');
    }
};
