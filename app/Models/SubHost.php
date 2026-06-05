<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubHost extends Model
{
    protected $fillable = [
        'name', 'address', 'port', 'sni', 'host', 'path', 'mode', 'security', 
        'insecure', 'alpn', 'extra', 'type', 'is_active', 'is_template', 'remark_prefix',
        'download_address', 'download_port', 'download_sni', 'is_reverse', 'cert_pem', 'pcs'
    ];

    protected $casts = [
        'extra' => 'array',
        'insecure' => 'boolean',
        'is_active' => 'boolean',
        'is_template' => 'boolean',
        'is_reverse' => 'boolean',
    ];
}
