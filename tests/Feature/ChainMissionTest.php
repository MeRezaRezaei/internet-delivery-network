<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\PhysicalPort;
use App\Models\XrayInbound;
use App\Models\XrayOutbound;
use App\Models\XrayRoutingRule;
use App\Services\Xray\Missions\ChainMission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChainMissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_split_routing_chain()
    {
        $node1 = Node::factory()->create(['name' => 'Edge']);
        $node2 = Node::factory()->create(['name' => 'Core']);

        $mission = new ChainMission();
        $result = $mission->setup([
            [
                'node' => $node1,
                'inbound_port' => 443,
                'inbound_port_ul' => 8443,
                'inbound_tag' => 'entry',
            ],
            [
                'node' => $node2,
                'inbound_port' => 10001,
                'inbound_port_ul' => 10002,
                'inbound_tag' => 'exit',
            ],
        ]);

        $this->assertCount(4, $result['inbounds']);
        $this->assertCount(3, $result['outbounds']);
        $this->assertCount(3, $result['routing_rules']);

        // Assert Physical Ports are reserved
        $this->assertDatabaseHas('physical_ports', [
            'node_id' => $node1->id,
            'port_number' => 443,
        ]);
        $this->assertDatabaseHas('physical_ports', [
            'node_id' => $node1->id,
            'port_number' => 8443,
        ]);

        // Assert Inbounds are created
        $this->assertDatabaseHas('xray_inbounds', [
            'tag' => 'entry-dl',
        ]);
        $this->assertDatabaseHas('xray_inbounds', [
            'tag' => 'entry-ul',
        ]);

        // Assert Outbounds are created
        $this->assertDatabaseHas('xray_outbounds', [
            'tag' => 'chain-out-to-exit-dl',
        ]);
        $this->assertDatabaseHas('xray_outbounds', [
            'tag' => 'chain-out-direct',
        ]);

        // Assert Routing Rules link previous tag to new outbound
        $this->assertDatabaseHas('xray_routing_rules', [
            'node_id' => $node1->id,
            'inbound_tags' => 'entry-dl',
            'outbound_tag' => 'chain-out-to-exit-dl',
        ]);

        // Assert IDN Tunnels are created
        $this->assertCount(1, $result['tunnels']);
        $this->assertDatabaseHas('idn_tunnels', [
            'source_node_id' => $node1->id,
            'target_node_id' => $node2->id,
            'protocol' => 'vless-chain',
        ]);
    }
}
