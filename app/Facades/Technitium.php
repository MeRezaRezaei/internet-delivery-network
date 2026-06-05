<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool setBlocklist(bool $enabled)
 * @method static bool updateRecord(string $domain, string $type, string $value)
 *
 * @see \App\Services\ControlPlane\TechnitiumService
 */
class Technitium extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'technitium';
    }
}
