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
    public function setup(Node $node, int $portNumber, string $tag, array $realityParams, string $transport = 'tcp', array $transportParams = []): XrayInbound
    {
        return DB::transaction(function () use ($node, $portNumber, $tag, $realityParams, $transport, $transportParams) {
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
            \App\Models\XrayProtocolVless::create([
                'handler_id' => $inbound->id,
                'handler_type' => XrayInbound::class,
                'decryption' => 'none',
            ]);

            // 5. Add REALITY if needed (only for TLS-like transports or direct TCP)
            if ($realityParams) {
                \App\Models\XraySecurityReality::create([
                    'handler_id' => $inbound->id,
                    'handler_type' => XrayInbound::class,
                    'dest' => $realityParams['dest'] ?? 'www.microsoft.com:443',
                    'server_names' => $realityParams['server_names'] ?? 'www.microsoft.com',
                    'private_key' => $realityParams['private_key'],
                    'short_ids' => $realityParams['short_ids'] ?? '01234567',
                ]);
            }

            // 6. Add Transport
            switch ($transport) {
                case 'xhttp':
                    \App\Models\XrayTransportXhttp::create([
                        'handler_id' => $inbound->id,
                        'handler_type' => XrayInbound::class,
                        'path' => $transportParams['path'] ?? '/',
                        'mode' => $transportParams['mode'] ?? 'packet-up',
                    ]);
                    break;
                case 'splithttp':
                    \App\Models\XrayTransportSplithttp::create([
                        'handler_id' => $inbound->id,
                        'handler_type' => XrayInbound::class,
                        'host' => $transportParams['host'] ?? null,
                        'path' => $transportParams['path'] ?? '/',
                        'mode' => $transportParams['mode'] ?? 'streaming',
                        'headers' => $transportParams['headers'] ?? null,
                    ]);
                    break;
                case 'httpupgrade':
                    \App\Models\XrayTransportHttpupgrade::create([
                        'handler_id' => $inbound->id,
                        'handler_type' => XrayInbound::class,
                        'host' => $transportParams['host'] ?? null,
                        'path' => $transportParams['path'] ?? '/',
                        'headers' => $transportParams['headers'] ?? null,
                    ]);
                    break;
                case 'grpc':
                    \App\Models\XrayTransportGrpc::create([
                        'handler_id' => $inbound->id,
                        'handler_type' => XrayInbound::class,
                        'service_name' => $transportParams['service_name'] ?? 'XraygRPC',
                    ]);
                    break;
            }

            return $inbound;
        });
    }
}
