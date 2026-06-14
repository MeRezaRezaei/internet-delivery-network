<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PhysicalPort extends Model
{
    use HasFactory;

    protected $table = 'idn_physical_ports';

    protected $fillable = [
        'node_id',
        'port_number',
        'protocol',
        'status',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    public function inbound(): HasOne
    {
        return $this->hasOne(XrayInbound::class, 'physical_port_id');
    }
}
