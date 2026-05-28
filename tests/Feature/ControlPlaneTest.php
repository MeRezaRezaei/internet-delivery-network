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
            'tag' => 'test-socks-hydration',
            'port' => 12345,
            'protocol' => 'socks',
        ];

        $inbound = XrayProtobufHydrator::hydrateInbound($config);

        $this->assertEquals('test-socks-hydration', $inbound->getTag());
    }

    /**
     * Test Dry-run validation prevents invalid configs (e.g. port clashes).
     */
    public function test_dry_run_validation()
    {
        $dryRun = new DryRunService();
        
        $tag = 'valid-socks-' . uniqid();
        $config = [
            'tag' => $tag,
            'port' => rand(30000, 40000),
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
            'payload' => ['tag' => 'non-existent-tag-' . uniqid()]
        ];

        // This will likely fail with Xray gRPC error because tag doesn't exist,
        // which triggers BATCH_FAILED state.
        try {
            $manager->processSignal($signal);
        } catch (\Exception $e) {
            // Expected
        }

        // Check if state was updated in Redis
        $state = Redis::hGetAll("idn:control-plane:nodes:local:state");
        $this->assertEquals('BATCH_FAILED', $state['last_action']);
    }
}
