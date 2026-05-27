<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\Xray\XrayService connection(string|null $name = null)
 * @method static \Xray\App\Proxyman\Command\HandlerServiceClient handler()
 * @method static \Xray\App\Stats\Command\StatsServiceClient stats()
 * @method static array getSysStats()
 * @method static array queryStats(string $pattern = "", bool $reset = false)
 * @method static bool addInbound(\Xray\App\Proxyman\InboundHandlerConfig $inbound)
 * @method static bool removeInbound(string $tag)
 * @method static bool ping()
 *
 * @see \App\Services\Xray\XrayManager
 */
class Xray extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'xray';
    }
}
