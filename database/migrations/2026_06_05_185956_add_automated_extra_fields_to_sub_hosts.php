<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_hosts', function (Blueprint $blueprint) {
            $blueprint->string('padding')->nullable()->after('extra');
            $blueprint->boolean('no_grpc_header')->default(false)->after('padding');
            $blueprint->string('sc_max_each_post_bytes')->nullable()->after('no_grpc_header');
            $blueprint->string('sc_min_posts_interval_ms')->nullable()->after('sc_max_each_post_bytes');
            $blueprint->integer('xmux_max_concurrency')->nullable()->after('sc_min_posts_interval_ms');
            $blueprint->integer('xmux_max_connections')->nullable()->after('xmux_max_concurrency');
            $blueprint->string('xmux_c_max_reuse_times')->nullable()->after('xmux_max_connections');
            $blueprint->string('xmux_h_max_request_times')->nullable()->after('xmux_c_max_reuse_times');
            $blueprint->string('xmux_h_max_reusable_secs')->nullable()->after('xmux_h_max_request_times');
            $blueprint->boolean('is_cdn')->default(false)->after('is_reverse');
        });
    }

    public function down(): void
    {
        Schema::table('sub_hosts', function (Blueprint $blueprint) {
            $blueprint->dropColumn([
                'padding', 'no_grpc_header', 'sc_max_each_post_bytes', 'sc_min_posts_interval_ms',
                'xmux_max_concurrency', 'xmux_max_connections', 'xmux_c_max_reuse_times',
                'xmux_h_max_request_times', 'xmux_h_max_reusable_secs', 'is_cdn'
            ]);
        });
    }
};
