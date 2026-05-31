<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XrayTransportHttpupgrade extends Model
{
    protected $table = 'xray_transport_httpupgrade';

    protected $fillable = [
        'handler_id',
        'handler_type',
        'host',
        'path',
        'headers',
        'accept_proxy_protocol',
        'ed',
    ];

    protected $casts = [
        'headers' => 'array',
        'accept_proxy_protocol' => 'boolean',
        'ed' => 'integer',
    ];

    public function handler(): MorphTo
    {
        return $this->morphTo();
    }
}
