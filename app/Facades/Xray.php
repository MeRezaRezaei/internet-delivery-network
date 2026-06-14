<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array generateConfig(\App\Models\Node $node)
 * @method static array validateNode(\App\Models\Node $node)
 * @method static mixed mission(string $name)
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
    protected static function getFacadeAccessor(): string
    {
        return 'xray.manager';
    }
}
