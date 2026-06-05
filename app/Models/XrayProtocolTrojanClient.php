<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XrayProtocolTrojanClient extends Model
{
    protected $fillable = [
        'trojan_id',
        'client_id',
        'flow',
    ];

    public function trojan(): BelongsTo
    {
        return $this->belongsTo(XrayProtocolTrojan::class, 'trojan_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(XrayClient::class, 'client_id');
    }
}
