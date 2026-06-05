<?php

namespace App\Models\Marzban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proxy extends Model
{
    protected $connection = 'marzban';
    protected $table = 'proxies';
    public $timestamps = false;

    protected $casts = [
        'settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
