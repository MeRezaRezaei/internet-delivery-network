<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\Tunnel;
use App\Models\XrayInbound;
use App\Models\XrayOutbound;
use App\Models\PhysicalPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TunnelVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_verify_a_tunnel_via_api()
    {
        $sourceNode = Node::factory()->create(['name' => 'source', 'ip' => '127.0.0.1']);
        $targetNode = Node::factory()->create(['name' => 'target', 'ip' => '127.0.0.1']);

        $port = PhysicalPort::factory()->create(['node_id' => $targetNode->id, 'port_number' => 443]);
        $inbound = XrayInbound::create(['physical_port_id' => $port->id, 'tag' => 'test-in']);
        $outbound = XrayOutbound::create(['node_id' => $sourceNode->id, 'tag' => 'test-out']);

        $tunnel = Tunnel::create([
            'source_node_id' => $sourceNode->id,
            'target_node_id' => $targetNode->id,
            'tag' => 'test-tunnel',
            'inbound_id' => $inbound->id,
            'outbound_id' => $outbound->id,
            'port' => 443,
            'protocol' => 'vless',
            'is_active' => true,
        ]);

        $response = $this->postJson("/idn/tunnels/{$tunnel->id}/verify");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'results' => [
                         'source' => ['success', 'output'],
                         'target' => ['success', 'output'],
                         'reachability'
                     ]
                 ]);
    }
}
