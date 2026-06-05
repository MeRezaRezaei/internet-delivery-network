<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XrayTransportSplithttp extends Model
{
    protected $table = 'xray_transport_splithttp';

    protected $fillable = [
        'handler_id',
        'handler_type',
        'host',
        'path',
        'mode',
        'headers',
        'x_padding_range',
        'x_padding_obfs_mode',
    ];

    protected $casts = [
        'headers' => 'array',
        'x_padding_obfs_mode' => 'boolean',
    ];

    public function handler(): MorphTo
    {
        return $this->morphTo();
    }
}
