<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubHost extends Model
{
    protected $fillable = [
        'name', 'address', 'port', 'sni', 'host', 'path', 'mode', 'security', 
        'insecure', 'alpn', 'extra', 'type', 'is_active', 'is_template', 'remark_prefix',
        'download_address', 'download_port', 'download_sni', 'is_reverse', 'cert_pem', 'pcs',
        'padding', 'no_grpc_header', 'sc_max_each_post_bytes', 'sc_min_posts_interval_ms',
        'xmux_max_concurrency', 'xmux_max_connections', 'xmux_c_max_reuse_times',
        'xmux_h_max_request_times', 'xmux_h_max_reusable_secs', 'is_cdn'
    ];

    protected $casts = [
        'extra' => 'array',
        'insecure' => 'boolean',
        'is_active' => 'boolean',
        'is_template' => 'boolean',
        'is_reverse' => 'boolean',
        'is_cdn' => 'boolean',
        'no_grpc_header' => 'boolean',
        'xmux_max_concurrency' => 'integer',
        'xmux_max_connections' => 'integer',
    ];
}
