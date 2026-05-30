<?php

namespace Tests\Feature\Safety;

use App\Models\Node;
use App\Services\Safety\RiskGuard;
use App\Services\Xray\XrayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Exception;

class RiskGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_risk_guard_blocks_server_07_ip()
    {
        $riskGuard = new RiskGuard();
        $node = Node::factory()->create([
            'ip' => '185.204.197.242', // Server 07 Public IP
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CRITICAL SECURITY VIOLATION: Access to Server 07 is blocked by RiskGuard policy.");

        $riskGuard->validateNodeAccess($node);
    }

    public function test_risk_guard_blocks_server_07_private_ip()
    {
        $riskGuard = new RiskGuard();
        $node = Node::factory()->create([
            'ip' => '10.255.1.7', // Server 07 Private IP
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CRITICAL SECURITY VIOLATION: Access to Server 07 is blocked by RiskGuard policy.");

        $riskGuard->validateNodeAccess($node);
    }

    public function test_risk_guard_blocks_server_07_domain()
    {
        $riskGuard = new RiskGuard();
        $node = Node::factory()->create([
            'hostname' => 'i-07.doctel.ir',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CRITICAL SECURITY VIOLATION: Access to Server 07 is blocked by RiskGuard policy.");

        $riskGuard->validateNodeAccess($node);
    }

    public function test_xray_manager_integration_with_risk_guard()
    {
        $node = Node::factory()->create([
            'ip' => '185.204.197.242',
        ]);

        $manager = app('xray.manager');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CRITICAL SECURITY VIOLATION: Access to Server 07 is blocked by RiskGuard policy.");

        $manager->connection($node);
    }

    public function test_risk_guard_allows_safe_nodes()
    {
        $riskGuard = new RiskGuard();
        $node = Node::factory()->create([
            'ip' => '1.1.1.1',
        ]);

        $riskGuard->validateNodeAccess($node);
        $this->assertTrue(true); // No exception thrown
    }

    public function test_risk_guard_blocks_restricted_ssh_ports_in_config()
    {
        $riskGuard = new RiskGuard();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CRITICAL SECURITY VIOLATION: Config attempts to bind to restricted management port 2022.");

        $riskGuard->validateConfig([
            'inbounds' => [
                ['port' => 8080],
                ['port' => 2022], // Restricted port
            ]
        ]);
    }

    public function test_risk_guard_allows_safe_ports_in_config()
    {
        $riskGuard = new RiskGuard();

        $riskGuard->validateConfig([
            'inbounds' => [
                ['port' => 8080],
                ['port' => 10085],
            ]
        ]);

        $this->assertTrue(true); // No exception thrown
    }

    public function test_node_observer_prevents_deletion_of_node_with_active_tunnels()
    {
        $sourceNode = Node::factory()->create();
        $targetNode = Node::factory()->create();

        \App\Models\Tunnel::factory()->create([
            'source_node_id' => $sourceNode->id,
            'target_node_id' => $targetNode->id,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("CRITICAL SECURITY VIOLATION: Risk Guard prevented deletion of Node [{$sourceNode->name}] because it has active tunnels.");

        $sourceNode->delete();
    }

    public function test_node_observer_allows_deletion_of_safe_node()
    {
        $node = Node::factory()->create();

        $node->delete();
        $this->assertDatabaseMissing('idn_nodes', ['id' => $node->id]);
    }
}
