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
        // Only run this in testing environment to prevent cluttering production DB
        if (!app()->environment('testing')) {
            return;
        }

        if (!Schema::hasTable('idn_nodes')) {
            Schema::create('idn_nodes', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('hostname');
                $table->string('ip')->nullable();
                $table->string('external_ip')->nullable();
                $table->integer('api_port')->default(10085);
                $table->string('role')->default('node');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_heartbeat_at')->nullable();
                $table->float('cpu_usage')->nullable();
                $table->float('ram_usage')->nullable();
                $table->integer('max_tunnels')->default(100);
                $table->string('os_type')->default('linux');
                $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('idn_physical_ports')) {
            Schema::create('idn_physical_ports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('node_id')->constrained('idn_nodes')->onDelete('cascade');
                $table->integer('port_number');
                $table->enum('protocol', ['tcp', 'udp'])->default('tcp');
                $table->enum('status', ['listening', 'reserved', 'free'])->default('free');
                $table->unique(['node_id', 'port_number', 'protocol']);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_sniffing_configs')) {
            Schema::create('xray_sniffing_configs', function (Blueprint $table) {
                $table->id();
                $table->boolean('enabled')->default(true);
                $table->string('dest_override')->default('http,tls');
                $table->boolean('route_only')->default(false);
                $table->boolean('metadata_only')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_policy_levels')) {
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
        }

        if (!Schema::hasTable('xray_inbounds')) {
            Schema::create('xray_inbounds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('physical_port_id')->constrained('idn_physical_ports')->onDelete('cascade');
                $table->string('tag')->unique();
                $table->foreignId('sniffing_id')->nullable()->constrained('xray_sniffing_configs')->onDelete('set null');
                $table->foreignId('policy_level_id')->nullable()->constrained('xray_policy_levels')->onDelete('set null');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_outbounds')) {
            Schema::create('xray_outbounds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('node_id')->constrained('idn_nodes')->onDelete('cascade');
                $table->string('tag')->unique();
                $table->string('send_through')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('idn_tunnels')) {
            Schema::create('idn_tunnels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_node_id');
                $table->unsignedBigInteger('target_node_id');
                $table->string('tag')->unique();
                $table->integer('port');
                $table->string('protocol')->default('vless');
                $table->json('config')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('inbound_id')->nullable();
                $table->unsignedBigInteger('outbound_id')->nullable();
                $table->unsignedBigInteger('inbound_ul_id')->nullable();
                $table->unsignedBigInteger('outbound_ul_id')->nullable();
                $table->timestamps();

                $table->foreign('source_node_id')->references('id')->on('idn_nodes')->onDelete('cascade');
                $table->foreign('target_node_id')->references('id')->on('idn_nodes')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('xray_clients')) {
            Schema::create('xray_clients', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('uuid')->unique();
                $table->string('secret')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_protocol_vless')) {
            Schema::create('xray_protocol_vless', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('handler_id');
                $table->string('handler_type'); // Morphic: inbound or outbound
                $table->string('decryption')->default('none');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_protocol_vless_clients')) {
            Schema::create('xray_protocol_vless_clients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vless_id')->constrained('xray_protocol_vless')->onDelete('cascade');
                $table->foreignId('client_id')->constrained('xray_clients')->onDelete('cascade');
                $table->string('flow')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_fallbacks')) {
            Schema::create('xray_fallbacks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inbound_id')->constrained('xray_inbounds')->onDelete('cascade');
                $table->string('name')->nullable();
                $table->string('path')->nullable();
                $table->string('alpn')->nullable();
                $table->string('dest_type')->default('port'); // port, unix, remote
                $table->string('dest_value'); // e.g., 80 or /tmp/nginx.sock
                $table->integer('xver')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_balancers')) {
            Schema::create('xray_balancers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('node_id')->constrained('idn_nodes')->onDelete('cascade');
                $table->string('tag')->unique();
                $table->text('selector'); // CSV of outbound tags
                $table->string('strategy')->default('random'); // random, leastPing, roundRobin
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_routing_rules')) {
            Schema::create('xray_routing_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('node_id')->constrained('idn_nodes')->onDelete('cascade');
                $table->integer('priority')->default(0);
                $table->string('type')->default('field');
                $table->text('inbound_tags')->nullable(); // CSV
                $table->string('outbound_tag');
                $table->string('domain_strategy')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_transport_splithttp')) {
            Schema::create('xray_transport_splithttp', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('handler_id');
                $table->string('handler_type'); // Morphic
                $table->string('host')->nullable();
                $table->string('path')->default('/');
                $table->string('mode')->default('streaming');
                $table->json('headers')->nullable();
                $table->string('x_padding_range')->nullable();
                $table->boolean('x_padding_obfs_mode')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_transport_httpupgrade')) {
            Schema::create('xray_transport_httpupgrade', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('handler_id');
                $table->string('handler_type'); // Morphic
                $table->string('host')->nullable();
                $table->string('path')->default('/');
                $table->json('headers')->nullable();
                $table->boolean('accept_proxy_protocol')->default(false);
                $table->unsignedInteger('ed')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_transport_xhttp')) {
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
        }

        if (!Schema::hasTable('xray_transport_grpc')) {
            Schema::create('xray_transport_grpc', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('handler_id');
                $table->string('handler_type'); // Morphic
                $table->string('service_name')->default('XraygRPC');
                $table->boolean('multi_mode')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_security_tls')) {
            Schema::create('xray_security_tls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('handler_id');
                $table->string('handler_type'); // Morphic
                $table->string('server_name')->nullable();
                $table->string('alpn')->default('h2,http/1.1');
                $table->boolean('allow_insecure')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('xray_security_reality')) {
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

        if (!Schema::hasTable('jwt')) {
            Schema::create('jwt', function (Blueprint $table) {
                $table->id();
                $table->string('secret_key')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!app()->environment('testing')) {
            return;
        }

        Schema::dropIfExists('jwt');
        Schema::dropIfExists('xray_security_reality');
        Schema::dropIfExists('xray_security_tls');
        Schema::dropIfExists('xray_transport_grpc');
        Schema::dropIfExists('xray_transport_xhttp');
        Schema::dropIfExists('xray_transport_httpupgrade');
        Schema::dropIfExists('xray_transport_splithttp');
        Schema::dropIfExists('xray_routing_rules');
        Schema::dropIfExists('xray_balancers');
        Schema::dropIfExists('xray_fallbacks');
        Schema::dropIfExists('xray_protocol_vless_clients');
        Schema::dropIfExists('xray_protocol_vless');
        Schema::dropIfExists('xray_clients');
        Schema::dropIfExists('idn_tunnels');
        Schema::dropIfExists('xray_outbounds');
        Schema::dropIfExists('xray_inbounds');
        Schema::dropIfExists('xray_policy_levels');
        Schema::dropIfExists('xray_sniffing_configs');
        Schema::dropIfExists('idn_physical_ports');
        Schema::dropIfExists('idn_nodes');
    }
};
