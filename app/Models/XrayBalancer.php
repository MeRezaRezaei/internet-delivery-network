<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\XrayStrategy;

class XrayBalancer extends Model
{
    protected $fillable = [
        'node_id',
        'tag',
        'selector',
        'strategy',
    ];

    protected $casts = [
        'strategy' => XrayStrategy::class,
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }
}
