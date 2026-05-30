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
        'path',
        'host',
        'max_upload_size',
        'max_concurrent_uploads',
    ];

    public function handler(): MorphTo
    {
        return $this->morphTo();
    }
}
