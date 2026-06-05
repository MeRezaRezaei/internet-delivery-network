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
        Schema::create('xray_transport_xhttp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handler_id');
            $table->string('handler_type'); // Morphic
            $table->string('path')->default('/');
            $table->string('mode')->default('packet-up');
            $table->string('padding_range')->nullable();
            $table->boolean('obfuscation_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('xray_transport_grpc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handler_id');
            $table->string('handler_type'); // Morphic
            $table->string('service_name')->default('XraygRPC');
            $table->boolean('multi_mode')->default(false);
            $table->timestamps();
        });

        Schema::create('xray_security_tls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handler_id');
            $table->string('handler_type'); // Morphic
            $table->string('server_name')->nullable();
            $table->string('alpn')->default('h2,http/1.1');
            $table->boolean('allow_insecure')->default(false);
            $table->timestamps();
        });

        Schema::create('xray_security_reality', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handler_id');
            $table->string('handler_type'); // Morphic
            $table->string('dest')->default('www.microsoft.com:443');
            $table->string('server_names')->nullable(); // CSV
            $table->string('private_key')->nullable();
            $table->string('short_ids')->nullable(); // CSV
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xray_security_reality');
        Schema::dropIfExists('xray_security_tls');
        Schema::dropIfExists('xray_transport_grpc');
        Schema::dropIfExists('xray_transport_xhttp');
    }
};
