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
        Schema::table('idn_nodes', function (Blueprint $table) {
            $table->float('cpu_usage')->nullable()->after('last_heartbeat_at');
            $table->float('ram_usage')->nullable()->after('cpu_usage');
            $table->integer('max_tunnels')->default(100)->after('ram_usage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idn_nodes', function (Blueprint $table) {
            $table->dropColumn(['cpu_usage', 'ram_usage', 'max_tunnels']);
        });
    }
};
