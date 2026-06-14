<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubProfile extends Model
{
    protected $fillable = [
        'name',
        'type', // 'tls', 'xhttp', 'xmux'
        'settings'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    /**
     * Get the sub hosts that use this TLS profile.
     */
    public function tlsHosts(): HasMany
    {
        return $this->hasMany(SubHost::class, 'tls_profile_id');
    }

    /**
     * Get the sub hosts that use this XHTTP profile.
     */
    public function xhttpHosts(): HasMany
    {
        return $this->hasMany(SubHost::class, 'xhttp_profile_id');
    }

    /**
     * Get the sub hosts that use this XMUX profile.
     */
    public function xmuxHosts(): HasMany
    {
        return $this->hasMany(SubHost::class, 'xmux_profile_id');
    }
}
