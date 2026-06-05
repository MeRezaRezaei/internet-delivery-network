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
        Schema::create('xray_protocol_trojan_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trojan_id')->constrained('xray_protocol_trojans')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('xray_clients')->onDelete('cascade');
            $table->string('flow')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xray_protocol_trojan_clients');
    }
};
