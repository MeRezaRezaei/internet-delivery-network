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
            $table->dropColumn('role');
        });

        Schema::table('idn_nodes', function (Blueprint $table) {
            $table->enum('role', ['dns', 'bridge', 'edge'])->default('bridge')->after('api_port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idn_nodes', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('idn_nodes', function (Blueprint $table) {
            $table->string('role')->default('node')->after('api_port');
        });
    }
};
