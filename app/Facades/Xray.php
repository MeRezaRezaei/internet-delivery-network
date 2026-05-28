<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array generateConfig(\App\Models\Node $node)
 * @method static array validateNode(\App\Models\Node $node)
 * @method static mixed mission(string $name)
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
