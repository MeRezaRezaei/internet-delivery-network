<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XrayTransportGrpc extends Model
{
    protected $table = 'xray_transport_grpc';

    protected $fillable = [
        'handler_id',
        'handler_type',
        'service_name',
        'multi_mode',
    ];

    protected $casts = [
        'multi_mode' => 'boolean',
    ];

    public function handler(): MorphTo
    {
        return $this->morphTo();
    }
}
