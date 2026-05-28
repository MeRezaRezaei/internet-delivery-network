<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XrayRoutingRule extends Model
{
    protected $fillable = [
        'node_id',
        'priority',
        'type',
        'inbound_tags',
        'outbound_tag',
        'domain_strategy',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
