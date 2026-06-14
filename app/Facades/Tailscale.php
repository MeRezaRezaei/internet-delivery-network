<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array devices()
 * @method static array device(string $id)
 * @method static array createAuthKey(array $capabilities = [], int $expirySeconds = 7776000)
 * @method static array acl()
 * @method static array updateAcl(array $acl)
 *
 * @see \App\Services\Tailscale\TailscaleService
 */
class Tailscale extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'tailscale';
    }
}
