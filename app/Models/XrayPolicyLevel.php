<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XrayPolicyLevel extends Model
{
    protected $fillable = [
        'node_id',
        'level_id',
        'handshake',
        'conn_idle',
        'buffer_size',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
