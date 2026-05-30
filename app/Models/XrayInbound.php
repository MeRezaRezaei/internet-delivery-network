<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class XrayInbound extends Model
{
    protected $fillable = [
        'physical_port_id',
        'tag',
        'sniffing_id',
        'policy_level_id',
    ];

    public function port(): BelongsTo
    {
        return $this->belongsTo(PhysicalPort::class, 'physical_port_id');
    }

    public function sniffing(): BelongsTo
    {
        return $this->belongsTo(XraySniffingConfig::class);
    }

    public function policyLevel(): BelongsTo
    {
        return $this->belongsTo(XrayPolicyLevel::class);
    }

    public function vless(): MorphOne
    {
        return $this->morphOne(XrayProtocolVless::class, 'handler');
    }

    public function trojan(): MorphOne
    {
        return $this->morphOne(XrayProtocolTrojan::class, 'handler');
    }

    public function xhttp(): MorphOne
    {
        return $this->morphOne(XrayTransportXhttp::class, 'handler');
    }

    public function splithttp(): MorphOne
    {
        return $this->morphOne(XrayTransportSplithttp::class, 'handler');
    }

    public function grpc(): MorphOne
    {
        return $this->morphOne(XrayTransportGrpc::class, 'handler');
    }

    public function tls(): MorphOne
    {
        return $this->morphOne(XraySecurityTls::class, 'handler');
    }

    public function reality(): MorphOne
    {
        return $this->morphOne(XraySecurityReality::class, 'handler');
    }

    public function fallbacks(): HasMany
    {
        return $this->hasMany(XrayFallback::class, 'inbound_id');
    }
}
