<?php

namespace App\Models\Marzban;

use Illuminate\Database\Eloquent\Model;

class Jwt extends Model
{
    protected $connection = 'marzban';
    protected $table = 'jwt';
}
