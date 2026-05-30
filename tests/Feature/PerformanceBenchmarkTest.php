<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Services\Xray\Missions\ChainMission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_benchmark_chain_mission_setup()
    {
        $node1 = Node::factory()->create(['name' => 'Edge']);
        $node2 = Node::factory()->create(['name' => 'Core']);
        $node3 = Node::factory()->create(['name' => 'Exit']);

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
                'inbound_tag' => 'core',
            ],
            [
                'node' => $node3,
                'inbound_port' => 10003,
                'inbound_port_ul' => 10004,
                'inbound_tag' => 'exit',
            ],
        ];

        $startTime = microtime(true);
        $mission->setup($hops);
        $endTime = microtime(true);

        $durationMs = ($endTime - $startTime) * 1000;

        echo "\n[BENCHMARK] ChainMission 3-hop setup took: " . round($durationMs, 2) . " ms\n";

        // Assert that 3 hops take less than 1500ms (database insert time can vary)
        $this->assertLessThan(1500, $durationMs, "Provisioning 3 hops should take less than 1500ms");
    }
}
