<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_hosts', function (Blueprint $blueprint) {
            $blueprint->string('download_address')->nullable();
            $blueprint->integer('download_port')->nullable();
            $blueprint->string('download_sni')->nullable();
            $blueprint->boolean('is_reverse')->default(false);
            $blueprint->text('cert_pem')->nullable();
            $blueprint->string('pcs')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sub_hosts', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['download_address', 'download_port', 'download_sni', 'is_reverse', 'cert_pem', 'pcs']);
        });
    }
};
