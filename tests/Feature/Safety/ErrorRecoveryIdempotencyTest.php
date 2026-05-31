<?php

namespace Tests\Feature\Safety;

use App\Models\Node;
use App\Models\PhysicalPort;
use App\Models\XrayInbound;
use App\Models\XrayOutbound;
use App\Models\XrayProtocolVless;
use App\Models\Tunnel;
use App\Services\Xray\Missions\ChainMission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Exception;
use Illuminate\Support\Facades\DB;

class ErrorRecoveryIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_chain_mission_rolls_back_entirely_on_failure()
    {
        $node1 = Node::factory()->create(['name' => 'Edge']);
        $node2 = Node::factory()->create(['name' => 'Core']);

        // Assert starting state
        $this->assertEquals(0, PhysicalPort::count());
        $this->assertEquals(0, XrayInbound::count());
        $this->assertEquals(0, Tunnel::count());

        $mission = new ChainMission();

        try {
            // We pass an invalid configuration for the second hop that will cause a DB error
            // For example, an inbound_port that violates a type constraint or something.
            // Actually, we can just throw an exception from an event or mock.
            // A simpler way: 'node' => null for the second hop will cause an error when it tries to get $node->id.
            $mission->setup([
                [
                    'node' => $node1,
                    'inbound_port' => 443,
                    'inbound_port_ul' => 8443,
                    'inbound_tag' => 'entry',
                ],
                [
                    'node' => null, // This will throw an Error when $node->id is accessed
                    'inbound_port' => 10001,
                    'inbound_port_ul' => 10002,
                    'inbound_tag' => 'exit',
                ],
            ]);
            $this->fail("Expected exception was not thrown.");
        } catch (\Throwable $e) {
            // Expected failure
        }

        // Assert everything was rolled back
        $this->assertEquals(0, PhysicalPort::count(), "Physical ports should have been rolled back");
        $this->assertEquals(0, XrayInbound::count(), "Inbounds should have been rolled back");
        $this->assertEquals(0, XrayOutbound::count(), "Outbounds should have been rolled back");
        $this->assertEquals(0, XrayProtocolVless::count(), "Protocols should have been rolled back");
        $this->assertEquals(0, Tunnel::count(), "Tunnels should have been rolled back");
    }

    public function test_chain_mission_is_idempotent_on_retry()
    {
        $node1 = Node::factory()->create(['name' => 'Edge']);
        $node2 = Node::factory()->create(['name' => 'Core']);

        $mission = new ChainMission();
        
        $hops = [
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
        ];

        // First attempt succeeds
        $mission->setup($hops);

        $initialInboundCount = XrayInbound::count();
        $initialTunnelCount = Tunnel::count();
        $initialOutboundCount = XrayOutbound::count();

        // Second attempt with exact same params should either throw a unique constraint violation 
        // or be handled cleanly without duplicating the tunnels.
        // Let's see what happens if we just run it again.
        try {
            $mission->setup($hops);
            // If it succeeds without throwing, we must verify no duplicates were created.
            // Currently ChainMission::setup does not check for existing tunnels by tag or port,
            // so it would create duplicates unless the DB schema prevents it.
            $this->assertEquals($initialInboundCount, XrayInbound::count(), "Should not duplicate inbounds");
            $this->assertEquals($initialTunnelCount, Tunnel::count(), "Should not duplicate tunnels");
        } catch (\Illuminate\Database\QueryException $e) {
            // If it throws a constraint violation (like unique tag), that's also an acceptable 
            // form of idempotency (fail safely).
            $this->assertStringContainsString('Integrity constraint violation', $e->getMessage());
            
            // State should remain unchanged
            $this->assertEquals($initialInboundCount, XrayInbound::count());
            $this->assertEquals($initialTunnelCount, Tunnel::count());
        }
    }
}
