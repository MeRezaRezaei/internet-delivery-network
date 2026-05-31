<?php
namespace App\Enums;

enum NodeRole: string
{
    case DNS = 'dns';
    case BRIDGE = 'bridge';
    case EDGE = 'edge';
    case PORTAL = 'portal';
    case EXIT = 'exit';
}
