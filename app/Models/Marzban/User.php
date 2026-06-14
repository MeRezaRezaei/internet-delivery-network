<?php

namespace App\Models\Marzban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $connection = 'marzban';
    protected $table = 'users';
    public $timestamps = false;

    public function proxies(): HasMany
    {
        return $this->hasMany(Proxy::class, 'user_id');
    }
}
