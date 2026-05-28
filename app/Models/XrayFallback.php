<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XrayFallback extends Model
{
    protected $fillable = [
        'inbound_id',
        'path',
        'alpn',
        'dest_type',
        'dest_value',
        'xver',
    ];

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(XrayInbound::class);
    }
}
