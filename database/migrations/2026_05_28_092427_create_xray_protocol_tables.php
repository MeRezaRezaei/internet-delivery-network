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
        Schema::create('xray_clients', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('uuid')->unique();
            $table->string('secret')->nullable();
            $table->timestamps();
        });

        Schema::create('xray_protocol_vless', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handler_id');
            $table->string('handler_type'); // Morphic: inbound or outbound
            $table->string('decryption')->default('none');
            $table->timestamps();
        });

        Schema::create('xray_protocol_vless_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vless_id')->constrained('xray_protocol_vless')->onDelete('cascade');
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
        Schema::dropIfExists('xray_protocol_vless_clients');
        Schema::dropIfExists('xray_protocol_vless');
        Schema::dropIfExists('xray_clients');
    }
};
