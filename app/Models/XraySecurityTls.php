<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XraySecurityTls extends Model
{
    protected $table = 'xray_security_tls';

    protected $fillable = [
        'handler_id',
        'handler_type',
        'server_name',
        'alpn',
        'allow_insecure',
    ];

    protected $casts = [
        'allow_insecure' => 'boolean',
    ];

    public function handler(): MorphTo
    {
        return $this->morphTo();
    }
}
