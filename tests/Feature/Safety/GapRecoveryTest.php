<?php

namespace Tests\Feature\Safety;

use App\Models\Node;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GapRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear Redis before each test
        Redis::flushdb();
    }

    public function test_gap_recovery_syncs_offline_node_from_redis_to_db()
    {
        // Simulate a gap where a node is marked active in DB but went offline
        $node = Node::factory()->create([
            'name' => 'srv-test-gap-1',
            'is_active' => true,
        ]);

        // Redis shows it offline
        Redis::hSet("idn:control-plane:nodes:{$node->name}:registry", 'healthy', 0);
        Redis::hSet("idn:control-plane:nodes:{$node->name}:registry", 'last_heartbeat', now()->subMinutes(5)->toIso8601String());

        // Run gap recovery (Reconciliation)
        $exitCode = Artisan::call('idn:fleet:reconcile', ['--fix' => true]);

        // Should return 1 due to finding discrepancies, but fix them
        $this->assertEquals(1, $exitCode);

        // Refresh DB node
        $node->refresh();

        // Should be fixed to inactive
        $this->assertFalse($node->is_active);
    }

    public function test_gap_recovery_creates_missing_node_from_redis()
    {
        $nodeName = 'srv-test-gap-missing';

        // Node exists in Redis but not DB
        Redis::hSet("idn:control-plane:nodes:{$nodeName}:registry", 'healthy', 1);
        Redis::hSet("idn:control-plane:nodes:{$nodeName}:registry", 'hostname', 'missing.doctel.ir');
        Redis::hSet("idn:control-plane:nodes:{$nodeName}:registry", 'last_heartbeat', now()->toIso8601String());

        $exitCode = Artisan::call('idn:fleet:reconcile', ['--fix' => true]);

        $this->assertEquals(1, $exitCode);

        $node = Node::where('name', $nodeName)->first();
        
        $this->assertNotNull($node);
        $this->assertEquals('missing.doctel.ir', $node->hostname);
        $this->assertTrue($node->is_active);
    }

    public function test_gap_recovery_reports_perfect_sync_when_no_gaps()
    {
        $node = Node::factory()->create([
            'name' => 'srv-test-gap-sync',
            'is_active' => true,
        ]);

        Redis::hSet("idn:control-plane:nodes:{$node->name}:registry", 'healthy', 1);
        Redis::hSet("idn:control-plane:nodes:{$node->name}:registry", 'last_heartbeat', now()->toIso8601String());

        $exitCode = Artisan::call('idn:fleet:reconcile', ['--fix' => true]);

        // 0 means no discrepancies
        $this->assertEquals(0, $exitCode);
    }
}
