<?php

namespace App\Services\Xray\Missions;

use App\Models\Node;
use App\Models\PhysicalPort;
use App\Models\XrayInbound;
use App\Models\XrayProtocolVless;
use App\Models\XraySecurityReality;
use App\Models\XraySniffingConfig;
use Illuminate\Support\Facades\DB;

class PortalMission
{
    public function setup(Node $node, int $portNumber, string $tag, array $realityParams): XrayInbound
    {
        return DB::transaction(function () use ($node, $portNumber, $tag, $realityParams) {
            // 1. Reserve Physical Port
            $port = PhysicalPort::firstOrCreate(
                ['node_id' => $node->id, 'port_number' => $portNumber, 'protocol' => 'tcp'],
                ['status' => 'reserved']
            );

            // 2. Create Sniffing Config
            $sniffing = XraySniffingConfig::create([
                'enabled' => true,
                'dest_override' => 'http,tls,quic,fakedns',
                'metadata_only' => false,
            ]);

            // 3. Create Inbound
            $inbound = XrayInbound::create([
                'physical_port_id' => $port->id,
                'tag' => $tag,
                'sniffing_id' => $sniffing->id,
            ]);

            // 4. Add VLESS
            XrayProtocolVless::create([
                'handler_id' => $inbound->id,
                'handler_type' => XrayInbound::class,
                'decryption' => 'none',
            ]);

            // 5. Add REALITY
            XraySecurityReality::create([
                'handler_id' => $inbound->id,
                'handler_type' => XrayInbound::class,
                'dest' => $realityParams['dest'] ?? 'www.microsoft.com:443',
                'server_names' => $realityParams['server_names'] ?? 'www.microsoft.com',
                'private_key' => $realityParams['private_key'],
                'short_ids' => $realityParams['short_ids'] ?? '01234567',
            ]);

            return $inbound;
        });
    }
}
