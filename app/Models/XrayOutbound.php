<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class XrayOutbound extends Model
{
    protected $fillable = [
        'node_id',
        'tag',
        'send_through',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
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
}
