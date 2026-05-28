<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XrayProtocolVless extends Model
{
    protected $table = 'xray_protocol_vless';

    protected $fillable = [
        'handler_id',
        'handler_type',
        'decryption',
    ];

    public function handler(): MorphTo
    {
        return $this->morphTo();
    }

    public function clients(): HasMany
    {
        return $this->hasMany(XrayProtocolVlessClient::class, 'vless_id');
    }
}
