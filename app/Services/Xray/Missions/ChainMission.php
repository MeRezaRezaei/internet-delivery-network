<?php

namespace App\Services\Xray\Missions;

use App\Models\Node;
use App\Models\PhysicalPort;
use App\Models\Tunnel;
use App\Models\XrayInbound;
use App\Models\XrayOutbound;
use App\Models\XrayProtocolVless;
use App\Models\XrayRoutingRule;
use App\Models\XraySniffingConfig;
use Illuminate\Support\Facades\DB;

class ChainMission
{
    /**
     * Provision a single tunnel across multiple hops in one atomic transaction,
     * separating download and upload paths for xHTTP aggregation.
     *
     * @param array $hops Array of configurations for each hop.
     *   [
     *     [
     *       'node' => Node model,
     *       'inbound_port' => int, // Used for download
     *       'inbound_port_ul' => int, // Used for upload (defaults to inbound_port + 1)
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
            $tunnels = [];

            $previousNode = null;
            $previousInboundTagDl = null;
            $previousInboundTagUl = null;

            foreach ($hops as $index => $hop) {
                /** @var Node $node */
                $node = $hop['node'];
                $portNumberDl = $hop['inbound_port'];
                $portNumberUl = $hop['inbound_port_ul'] ?? ($portNumberDl + 1);
                $baseTag = $hop['inbound_tag'] ?? "chain-in-{$index}";
                
                $inboundTagDl = "{$baseTag}-dl";
                $inboundTagUl = "{$baseTag}-ul";

                // 1. Reserve Physical Ports
                $portDl = PhysicalPort::firstOrCreate(
                    ['node_id' => $node->id, 'port_number' => $portNumberDl, 'protocol' => 'tcp'],
                    ['status' => 'reserved']
                );
                $portUl = PhysicalPort::firstOrCreate(
                    ['node_id' => $node->id, 'port_number' => $portNumberUl, 'protocol' => 'tcp'],
                    ['status' => 'reserved']
                );

                // 2. Create Sniffing Config (useful for portal/entry)
                $sniffing = XraySniffingConfig::create([
                    'enabled' => true,
                    'dest_override' => 'http,tls,quic,fakedns',
                    'metadata_only' => false,
                ]);

                // 3. Create Inbounds
                $inboundDl = XrayInbound::create([
                    'physical_port_id' => $portDl->id,
                    'tag' => $inboundTagDl,
                    'sniffing_id' => $sniffing->id,
                ]);
                $inboundUl = XrayInbound::create([
                    'physical_port_id' => $portUl->id,
                    'tag' => $inboundTagUl,
                    'sniffing_id' => $sniffing->id,
                ]);

                // 4. Add VLESS to Inbounds
                XrayProtocolVless::create([
                    'handler_id' => $inboundDl->id,
                    'handler_type' => XrayInbound::class,
                    'decryption' => 'none',
                ]);
                XrayProtocolVless::create([
                    'handler_id' => $inboundUl->id,
                    'handler_type' => XrayInbound::class,
                    'decryption' => 'none',
                ]);

                $inbounds[] = $inboundDl;
                $inbounds[] = $inboundUl;

                // 5. Link previous hop to this hop
                if ($previousNode) {
                    // Outbounds from previous node to this node
                    $outboundDl = XrayOutbound::create([
                        'node_id' => $previousNode->id,
                        'tag' => "chain-out-to-{$inboundTagDl}",
                    ]);
                    $outboundUl = XrayOutbound::create([
                        'node_id' => $previousNode->id,
                        'tag' => "chain-out-to-{$inboundTagUl}",
                    ]);

                    XrayProtocolVless::create([
                        'handler_id' => $outboundDl->id,
                        'handler_type' => XrayOutbound::class,
                        'decryption' => 'none',
                    ]);
                    XrayProtocolVless::create([
                        'handler_id' => $outboundUl->id,
                        'handler_type' => XrayOutbound::class,
                        'decryption' => 'none',
                    ]);

                    $outbounds[] = $outboundDl;
                    $outbounds[] = $outboundUl;

                    // Route from previous inbound to this new outbound
                    $routingRules[] = XrayRoutingRule::create([
                        'node_id' => $previousNode->id,
                        'priority' => 10,
                        'type' => 'field',
                        'inbound_tags' => $previousInboundTagDl,
                        'outbound_tag' => $outboundDl->tag,
                    ]);
                    $routingRules[] = XrayRoutingRule::create([
                        'node_id' => $previousNode->id,
                        'priority' => 10,
                        'type' => 'field',
                        'inbound_tags' => $previousInboundTagUl,
                        'outbound_tag' => $outboundUl->tag,
                    ]);

                    // 6. Create IDN Tunnel record for this hop
                    $tunnels[] = Tunnel::create([
                        'source_node_id' => $previousNode->id,
                        'target_node_id' => $node->id,
                        'tag' => "{$previousInboundTagDl}-to-{$inboundTagDl}",
                        'port' => $portNumberDl,
                        'protocol' => 'vless-chain',
                        'config' => [
                            'inbound_dl_tag' => $inboundTagDl,
                            'inbound_ul_tag' => $inboundTagUl,
                            'outbound_dl_tag' => $outboundDl->tag,
                            'outbound_ul_tag' => $outboundUl->tag,
                        ],
                        'is_active' => true,
                    ]);
                }

                $previousNode = $node;
                $previousInboundTagDl = $inboundTagDl;
                $previousInboundTagUl = $inboundTagUl;
            }

            // On the last node, create direct outbounds (freedom) and route traffic to it
            if ($previousNode) {
                $finalOutboundTag = "chain-out-direct";
                $outbound = XrayOutbound::create([
                    'node_id' => $previousNode->id,
                    'tag' => $finalOutboundTag,
                ]);

                $outbounds[] = $outbound;

                $routingRules[] = XrayRoutingRule::create([
                    'node_id' => $previousNode->id,
                    'priority' => 10,
                    'type' => 'field',
                    'inbound_tags' => "{$previousInboundTagDl},{$previousInboundTagUl}",
                    'outbound_tag' => $finalOutboundTag,
                ]);
            }

            return [
                'inbounds' => $inbounds,
                'outbounds' => $outbounds,
                'routing_rules' => $routingRules,
                'tunnels' => $tunnels,
            ];
        });
    }
}
