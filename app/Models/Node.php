<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    use HasFactory;

    protected $table = 'idn_nodes';

    protected $fillable = [
        'name',
        'hostname',
        'ip',
        'external_ip',
        'api_port',
        'role',
        'is_active',
        'last_heartbeat_at',
        'os_type',
        'status',
        'metadata',
    ];

    protected $casts = [
        'role' => \App\Enums\NodeRole::class,
        'is_active' => 'boolean',
        'last_heartbeat_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function ports(): HasMany
    {
        return $this->hasMany(PhysicalPort::class);
    }

    public function outbounds(): HasMany
    {
        return $this->hasMany(XrayOutbound::class);
    }

    public function balancers(): HasMany
    {
        return $this->hasMany(XrayBalancer::class);
    }

    public function routingRules(): HasMany
    {
        return $this->hasMany(XrayRoutingRule::class);
    }

    public function policyLevels(): HasMany
    {
        return $this->hasMany(XrayPolicyLevel::class);
    }

    public function sourceTunnels(): HasMany
    {
        return $this->hasMany(Tunnel::class, 'source_node_id');
    }

    public function targetTunnels(): HasMany
    {
        return $this->hasMany(Tunnel::class, 'target_node_id');
    }
}
