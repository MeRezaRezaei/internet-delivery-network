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
        'inbound_id',
        'outbound_id',
        'inbound_ul_id',
        'outbound_ul_id',
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

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(XrayInbound::class, 'inbound_id');
    }

    public function outbound(): BelongsTo
    {
        return $this->belongsTo(XrayOutbound::class, 'outbound_id');
    }

    public function inboundUl(): BelongsTo
    {
        return $this->belongsTo(XrayInbound::class, 'inbound_ul_id');
    }

    public function outboundUl(): BelongsTo
    {
        return $this->belongsTo(XrayOutbound::class, 'outbound_ul_id');
    }
}
