<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class XrayClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'uuid',
        'secret',
    ];

    public function vlessClients(): HasMany
    {
        return $this->hasMany(XrayProtocolVlessClient::class, 'client_id');
    }
}
