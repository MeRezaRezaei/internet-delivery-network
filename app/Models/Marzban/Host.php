<?php

namespace App\Models\Marzban;

use Illuminate\Database\Eloquent\Model;

class Host extends Model
{
    protected $connection = 'marzban';
    protected $table = 'hosts';
    public $timestamps = false;
}
