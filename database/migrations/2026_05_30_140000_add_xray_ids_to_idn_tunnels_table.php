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
        Schema::table('idn_tunnels', function (Blueprint $table) {
            $table->foreignId('inbound_id')->nullable()->after('tag')->constrained('xray_inbounds')->onDelete('set null');
            $table->foreignId('outbound_id')->nullable()->after('inbound_id')->constrained('xray_outbounds')->onDelete('set null');
            $table->foreignId('inbound_ul_id')->nullable()->after('outbound_id')->constrained('xray_inbounds')->onDelete('set null');
            $table->foreignId('outbound_ul_id')->nullable()->after('inbound_ul_id')->constrained('xray_outbounds')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idn_tunnels', function (Blueprint $table) {
            $table->dropForeign(['inbound_id']);
            $table->dropForeign(['outbound_id']);
            $table->dropForeign(['inbound_ul_id']);
            $table->dropForeign(['outbound_ul_id']);
            $table->dropColumn(['inbound_id', 'outbound_id', 'inbound_ul_id', 'outbound_ul_id']);
        });
    }
};
