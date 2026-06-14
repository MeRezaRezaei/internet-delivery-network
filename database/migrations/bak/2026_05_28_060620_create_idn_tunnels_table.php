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
        Schema::create('idn_tunnels', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('source_node_id')->constrained('idn_nodes')->onDelete('cascade');
            $blueprint->foreignId('target_node_id')->constrained('idn_nodes')->onDelete('cascade');
            $blueprint->string('tag')->unique();
            $blueprint->integer('port');
            $blueprint->string('protocol')->default('vless');
            $blueprint->json('config')->nullable();
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idn_tunnels');
    }
};
