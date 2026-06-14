<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XrayTransportXhttp extends Model
{
    protected $table = 'xray_transport_xhttp';

    protected $fillable = [
        'handler_id',
        'handler_type',
        'path',
        'mode',
        'padding_range',
        'obfuscation_enabled',
    ];

    protected $casts = [
        'obfuscation_enabled' => 'boolean',
    ];

    public function handler(): MorphTo
    {
        return $this->morphTo();
    }
}
