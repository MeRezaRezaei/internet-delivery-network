<?php

namespace App\Services\Xray\Missions;

use App\Models\IDN\Node;
use App\Models\PhysicalPort;
use App\Models\XrayInbound;
use App\Models\XrayOutbound;
use App\Models\XrayProtocolVless;
use App\Models\XrayRoutingRule;
use App\Models\XraySniffingConfig;
use Illuminate\Support\Facades\DB;

class ChainMission
{
    /**
     * Provision a single tunnel across multiple hops in one atomic transaction.
     *
     * @param array $hops Array of configurations for each hop.
     *   [
     *     [
     *       'node' => Node model,
     *       'inbound_port' => int,
     *       'inbound_tag' => string,
     *     ],
     *     ...
     *   ]
     * @return array
     */
    public function setup(array $hops): array
    {
        return DB::transaction(function () use ($hops) {
            $inbounds = [];
            $outbounds = [];
            $routingRules = [];

            $previousNode = null;
            $previousInboundTag = null;

            foreach ($hops as $index => $hop) {
                /** @var Node $node */
                $node = $hop['node'];
                $portNumber = $hop['inbound_port'];
                $inboundTag = $hop['inbound_tag'] ?? "chain-in-{$index}";
                $outboundTag = "chain-out-to-" . ($index + 1);

                // 1. Reserve Physical Port
                $port = PhysicalPort::firstOrCreate(
                    ['node_id' => $node->id, 'port_number' => $portNumber, 'protocol' => 'tcp'],
                    ['status' => 'reserved']
                );

                // 2. Create Sniffing Config (especially useful for portal/entry)
                $sniffing = XraySniffingConfig::create([
                    'enabled' => true,
                    'dest_override' => 'http,tls,quic,fakedns',
                    'metadata_only' => false,
                ]);

                // 3. Create Inbound
                $inbound = XrayInbound::create([
                    'physical_port_id' => $port->id,
                    'tag' => $inboundTag,
                    'sniffing_id' => $sniffing->id,
                ]);

                // 4. Add VLESS to Inbound
                XrayProtocolVless::create([
                    'handler_id' => $inbound->id,
                    'handler_type' => XrayInbound::class,
                    'decryption' => 'none',
                ]);

                $inbounds[] = $inbound;

                // 5. Link previous hop to this hop
                if ($previousNode) {
                    $outbound = XrayOutbound::create([
                        'node_id' => $previousNode->id,
                        'tag' => "chain-out-to-{$inboundTag}",
                    ]);

                    XrayProtocolVless::create([
                        'handler_id' => $outbound->id,
                        'handler_type' => XrayOutbound::class,
                        'decryption' => 'none',
                    ]);

                    $outbounds[] = $outbound;

                    // Route from previous inbound to this new outbound
                    $routingRule = XrayRoutingRule::create([
                        'node_id' => $previousNode->id,
                        'priority' => 10,
                        'type' => 'field',
                        'inbound_tags' => $previousInboundTag,
                        'outbound_tag' => $outbound->tag,
                    ]);

                    $routingRules[] = $routingRule;
                }

                $previousNode = $node;
                $previousInboundTag = $inboundTag;
            }

            // On the last node, create a direct outbound (freedom) and route traffic to it
            if ($previousNode) {
                $finalOutboundTag = "chain-out-direct";
                $outbound = XrayOutbound::create([
                    'node_id' => $previousNode->id,
                    'tag' => $finalOutboundTag,
                ]);

                $outbounds[] = $outbound;

                $routingRule = XrayRoutingRule::create([
                    'node_id' => $previousNode->id,
                    'priority' => 10,
                    'type' => 'field',
                    'inbound_tags' => $previousInboundTag,
                    'outbound_tag' => $finalOutboundTag,
                ]);

                $routingRules[] = $routingRule;
            }

            return [
                'inbounds' => $inbounds,
                'outbounds' => $outbounds,
                'routing_rules' => $routingRules,
            ];
        });
    }
}
