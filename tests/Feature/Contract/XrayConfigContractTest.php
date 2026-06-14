<?php

namespace Tests\Feature\Contract;

use App\Models\Node;
use App\Services\Xray\XrayConfigRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XrayConfigContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the rendered Xray config follows the mandatory structure contract.
     */
    public function test_xray_config_base_contract()
    {
        $node = Node::factory()->create([
            'hostname' => 'contract-node',
            'ip' => '1.1.1.1'
        ]);

        $renderer = new XrayConfigRenderer();
        $config = $renderer->render($node);

        $this->assertIsArray($config);
        
        // Mandatory top-level keys for IDN-Xray compatibility
        $this->assertArrayHasKey('log', $config);
        $this->assertArrayHasKey('api', $config);
        $this->assertArrayHasKey('stats', $config);
        $this->assertArrayHasKey('policy', $config);
        $this->assertArrayHasKey('inbounds', $config);
        $this->assertArrayHasKey('outbounds', $config);
        $this->assertArrayHasKey('routing', $config);

        // Verify loglevel contract
        $this->assertEquals('debug', $config['log']['loglevel']);

        // Verify API contract
        $this->assertEquals('api', $config['api']['tag']);
        $this->assertContains('HandlerService', $config['api']['services']);
        $this->assertContains('StatsService', $config['api']['services']);

        // Verify mandatory API inbound exists on 127.0.0.1:10085
        $apiInbound = collect($config['inbounds'])->firstWhere('tag', 'api');
        $this->assertNotNull($apiInbound);
        $this->assertEquals('127.0.0.1', $apiInbound['listen']);
        $this->assertEquals(10085, $apiInbound['port']);
        $this->assertEquals('dokodemo-door', $apiInbound['protocol']);
    }

    /**
     * Test that the rendered Xray config is deterministic.
     */
    public function test_xray_config_is_deterministic()
    {
        $node = Node::factory()->create();
        
        // Add multiple ports, outbounds, balancers in random order
        $node->ports()->create(['port_number' => 8080, 'protocol' => 'tcp']);
        $node->ports()->create(['port_number' => 9090, 'protocol' => 'tcp']);
        $node->ports()->create(['port_number' => 7070, 'protocol' => 'tcp']);

        $renderer = new XrayConfigRenderer();
        
        $config1 = $renderer->render($node);
        $config2 = $renderer->render($node);

        $this->assertEquals(json_encode($config1), json_encode($config2));
    }
}
