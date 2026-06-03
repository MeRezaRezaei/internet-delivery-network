<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tunnel extends Model
{
    protected $table = 'idn_tunnels';

    protected $fillable = [
        'source_node_id',
        'target_node_id',
        'tag',
        'port',
        'protocol',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    public function sourceNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'source_node_id');
    }

    public function targetNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'target_node_id');
    }

    public function targetDownloadInbound(): ?XrayInbound
    {
        return $this->xrayInboundFromConfig('inbound_dl_tag');
    }

    public function targetUploadInbound(): ?XrayInbound
    {
        return $this->xrayInboundFromConfig('inbound_ul_tag');
    }

    public function sourceDownloadOutbound(): ?XrayOutbound
    {
        return $this->xrayOutboundFromConfig('outbound_dl_tag');
    }

    public function sourceUploadOutbound(): ?XrayOutbound
    {
        return $this->xrayOutboundFromConfig('outbound_ul_tag');
    }

    protected function xrayInboundFromConfig(string $key): ?XrayInbound
    {
        $tag = $this->config[$key] ?? null;

        if (!$tag) {
            return null;
        }

        return XrayInbound::query()
            ->where('tag', $tag)
            ->whereHas('port', fn ($query) => $query->where('node_id', $this->target_node_id))
            ->first();
    }

    protected function xrayOutboundFromConfig(string $key): ?XrayOutbound
    {
        $tag = $this->config[$key] ?? null;

        if (!$tag) {
            return null;
        }

        return XrayOutbound::query()
            ->where('tag', $tag)
            ->where('node_id', $this->source_node_id)
            ->first();
    }
}
