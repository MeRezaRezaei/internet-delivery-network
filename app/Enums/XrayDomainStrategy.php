<?php

namespace App\Enums;

enum XrayDomainStrategy: string
{
    case AS_IS = 'AsIs';
    case IP_IF_NON_MATCH = 'IPIfNonMatch';
    case IP_ON_DEMAND = 'IPOnDemand';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
