<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubHost extends Model
{
    protected $fillable = [
        'name', 'address', 'port', 'sni', 'host', 'path', 'mode', 'security', 
        'insecure', 'alpn', 'extra', 'type', 'is_active', 'remark_prefix'
    ];

    protected $casts = [
        'extra' => 'array',
        'insecure' => 'boolean',
        'is_active' => 'boolean',
    ];
}
