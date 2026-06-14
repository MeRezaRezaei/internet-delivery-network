<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XraySniffingConfig extends Model
{
    protected $fillable = [
        'enabled',
        'dest_override',
        'route_only',
        'metadata_only',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'route_only' => 'boolean',
        'metadata_only' => 'boolean',
    ];
}
