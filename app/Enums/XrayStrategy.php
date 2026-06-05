<?php

namespace App\Enums;

enum XrayStrategy: string
{
    case RANDOM = 'random';
    case LEAST_PING = 'leastPing';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
