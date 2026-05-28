<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XrayBalancer extends Model
{
    protected $fillable = [
        'node_id',
        'tag',
        'selector',
        'strategy',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
