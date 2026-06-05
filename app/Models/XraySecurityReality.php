<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XraySecurityReality extends Model
{
    protected $table = 'xray_security_reality';

    protected $fillable = [
        'handler_id',
        'handler_type',
        'dest',
        'server_names',
        'private_key',
        'short_ids',
    ];

    public function handler(): MorphTo
    {
        return $this->morphTo();
    }
}
