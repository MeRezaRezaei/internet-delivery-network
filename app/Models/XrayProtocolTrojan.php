<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XrayProtocolTrojan extends Model
{
    protected $fillable = [
        'handler_id',
        'handler_type',
    ];

    public function handler(): MorphTo
    {
        return $this->morphTo();
    }

    public function clients(): HasMany
    {
        return $this->hasMany(XrayProtocolTrojanClient::class, 'trojan_id');
    }
}
