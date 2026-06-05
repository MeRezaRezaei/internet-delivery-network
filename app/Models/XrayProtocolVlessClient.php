<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XrayProtocolVlessClient extends Model
{
    protected $fillable = [
        'vless_id',
        'client_id',
        'flow',
    ];

    public function vless(): BelongsTo
    {
        return $this->belongsTo(XrayProtocolVless::class, 'vless_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(XrayClient::class, 'client_id');
    }
}
