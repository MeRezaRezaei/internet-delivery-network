<?php

namespace App\Models\IDN;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    protected $table = 'idn_nodes';

    protected $fillable = [
        'name',
        'hostname',
        'ip',
        'api_port',
        'role',
        'is_active',
        'last_heartbeat_at',
        'metadata',
    ];

    protected $casts = [
        'role' => \App\Enums\NodeRole::class,
        'is_active' => 'boolean',
        'last_heartbeat_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function sourceTunnels(): HasMany
    {
        return $this->hasMany(Tunnel::class, 'source_node_id');
    }

    public function targetTunnels(): HasMany
    {
        return $this->hasMany(Tunnel::class, 'target_node_id');
    }
}
