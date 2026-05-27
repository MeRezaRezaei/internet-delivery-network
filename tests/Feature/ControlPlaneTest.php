<?php

namespace Tests\Feature;

use App\Services\ControlPlane\ControlPlaneManager;
use App\Services\ControlPlane\DryRunService;
use App\Utils\XrayProtobufHydrator;
use App\Facades\Xray;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ControlPlaneTest extends TestCase
{
    /**
     * Test Protobuf Hydration for SOCKS inbounds.
     */
    public function test_inbound_hydration()
    {
        $config = [
            'tag' => 'test-socks',
            'port' => 12345,
            'protocol' => 'socks',
        ];

        $inbound = XrayProtobufHydrator::hydrateInbound($config);

        $this->assertEquals('test-socks', $inbound->getTag());
    }

    /**
     * Test Dry-run validation prevents invalid configs (e.g. port clashes).
     */
    public function test_dry_run_validation()
    {
        $dryRun = new DryRunService();
        
        $config = [
            'tag' => 'valid-socks',
            'port' => 30000,
            'protocol' => 'socks',
        ];
        
        $inbound = XrayProtobufHydrator::hydrateInbound($config);
        
        // This should pass if dry_run instance is healthy
        $this->assertTrue($dryRun->validateInbound($inbound));
    }

    /**
     * Test full signal processing flow.
     */
    public function test_signal_processing_success()
    {
        $manager = app(ControlPlaneManager::class);

        $signal = [
            'action' => 'REMOVE_INBOUND',
            'node' => 'local',
            'payload' => ['tag' => 'non-existent-tag']
        ];

        // This might fail if the tag doesn't exist, but we want to see it reaching the Xray service.
        try {
            $manager->processSignal($signal);
        } catch (\Exception $e) {
            // Expected failure from Xray side is fine, as long as it reaches the service.
            $this->assertStringContainsString('Xray gRPC Error', $e->getMessage());
        }

        // Check if state was updated in Redis
        $state = Redis::hGetAll("idn:control-plane:nodes:local:state");
        $this->assertEquals('REMOVE_INBOUND', $state['last_action']);
    }
}
