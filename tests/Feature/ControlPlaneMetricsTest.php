<?php

namespace Tests\Feature;

use App\Console\Commands\IDN\ControlPlaneListenCommand;
use App\Models\Node;
use App\Services\ControlPlane\ControlPlaneManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ControlPlaneMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_control_plane_listen_updates_resource_metrics_in_db()
    {
        $nodeName = 'test-metrics-node';

        $node = Node::factory()->create([
            'name' => $nodeName,
            'cpu_usage' => 0.0,
            'ram_usage' => 0.0,
        ]);

        $manager = $this->createMock(ControlPlaneManager::class);

        // Expose protected methods for testing using an anonymous class
        $command = new class($manager) extends ControlPlaneListenCommand {
            public function testUpdateHeartbeat($nodeName) {
                $this->nodeName = $nodeName;
                $this->updateHeartbeat();
            }
        };

        $command->testUpdateHeartbeat($nodeName);

        $node->refresh();

        $this->assertNotNull($node->cpu_usage);
        $this->assertNotNull($node->ram_usage);
        
        $redisCpu = Redis::hGet("idn:control-plane:nodes:{$nodeName}:registry", 'cpu_usage');
        $redisRam = Redis::hGet("idn:control-plane:nodes:{$nodeName}:registry", 'ram_usage');

        $this->assertEquals((string)$node->cpu_usage, $redisCpu);
        $this->assertEquals((string)$node->ram_usage, $redisRam);
    }
}
