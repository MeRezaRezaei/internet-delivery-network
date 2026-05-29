<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_ports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('node_id');
            $table->integer('port_number');
            $table->string('protocol');
            $table->string('status')->default('free');
            $table->timestamps();
        });

        Schema::create('xray_sniffing_configs', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('dest_override')->nullable();
            $table->boolean('metadata_only')->default(false);
            $table->timestamps();
        });

        Schema::create('xray_inbounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('physical_port_id');
            $table->string('tag');
            $table->unsignedBigInteger('sniffing_id')->nullable();
            $table->timestamps();
        });

        Schema::create('xray_outbounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('node_id');
            $table->string('tag');
            $table->timestamps();
        });

        Schema::create('xray_protocol_vless', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handler_id');
            $table->string('handler_type');
            $table->string('decryption')->default('none');
            $table->timestamps();
        });

        Schema::create('xray_routing_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('node_id');
            $table->integer('priority')->default(0);
            $table->string('type')->default('field');
            $table->string('inbound_tags')->nullable();
            $table->string('outbound_tag');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xray_routing_rules');
        Schema::dropIfExists('xray_protocol_vless');
        Schema::dropIfExists('xray_outbounds');
        Schema::dropIfExists('xray_inbounds');
        Schema::dropIfExists('xray_sniffing_configs');
        Schema::dropIfExists('physical_ports');
    }
};
