<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\IDN\Technitium\TechnitiumClient;

/**
 * @method static \App\Services\IDN\Technitium\Modules\ZoneModule zones()
 * @method static \App\Services\IDN\Technitium\Modules\RecordModule records()
 * @method static \App\Services\IDN\Technitium\Modules\SettingsModule settings()
 * @method static \App\Services\IDN\Technitium\Modules\ClusterModule cluster()
 * @method static \App\Services\IDN\Technitium\Modules\UserModule user()
 * @method static string login()
 * @method static \Illuminate\Http\Client\Response request(string $endpoint, array $params = [], string $method = 'GET')
 * 
 * @see \App\Services\IDN\Technitium\TechnitiumClient
 */
class Technitium extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return TechnitiumClient::class;
    }
}
