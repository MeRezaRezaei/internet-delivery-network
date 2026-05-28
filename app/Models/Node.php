<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostname',
        'internal_ip',
        'external_ip',
        'os_type',
        'status',
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
}
