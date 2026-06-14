<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubHost extends Model
{
    protected $fillable = [
        'name', 'address', 'port', 'sni', 'host', 'path', 'mode', 'security', 
        'insecure', 'alpn', 'extra', 'type', 'is_active', 'is_template', 'remark_prefix',
        'download_address', 'download_port', 'download_sni', 'is_reverse', 'cert_pem', 'pcs',
        'padding', 'no_grpc_header', 'sc_max_each_post_bytes', 'sc_min_posts_interval_ms',
        'xmux_max_concurrency', 'xmux_max_connections', 'xmux_c_max_reuse_times',
        'xmux_h_max_request_times', 'xmux_h_max_reusable_secs', 'is_cdn', 'flow',
        'tls_profile_id', 'xhttp_profile_id', 'xmux_profile_id', 'download_host_id', 'is_dev'
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
        'is_dev' => 'boolean',
        'tls_profile_id' => 'integer',
        'xhttp_profile_id' => 'integer',
        'xmux_profile_id' => 'integer',
        'download_host_id' => 'integer',
    ];

    /**
     * TLS Profile relationship.
     */
    public function tlsProfile(): BelongsTo
    {
        return $this->belongsTo(SubProfile::class, 'tls_profile_id');
    }

    /**
     * XHTTP Profile relationship.
     */
    public function xhttpProfile(): BelongsTo
    {
        return $this->belongsTo(SubProfile::class, 'xhttp_profile_id');
    }

    /**
     * XMUX Profile relationship.
     */
    public function xmuxProfile(): BelongsTo
    {
        return $this->belongsTo(SubProfile::class, 'xmux_profile_id');
    }

    /**
     * Download Host relationship (self-relation).
     */
    public function downloadHost(): BelongsTo
    {
        return $this->belongsTo(SubHost::class, 'download_host_id');
    }

    /**
     * Upload Hosts relationship (self-relation).
     */
    public function uploadHosts(): HasMany
    {
        return $this->hasMany(SubHost::class, 'download_host_id');
    }
}
