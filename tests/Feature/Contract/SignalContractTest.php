<?php

namespace Tests\Feature\Contract;

use App\Services\ControlPlane\SignalDispatcher;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class SignalContractTest extends TestCase
{
    /**
     * Test that signals dispatched to Redis match the expected contract for the nodes.
     */
    public function test_signal_dispatch_contract()
    {
        Redis::shouldReceive('executeRaw')
            ->once()
            ->withArgs(function ($args) {
                // Expected format: [XADD, key, MAXLEN, ~, 1000, *, node, node_name, action, action_name, payload, json_payload, timestamp, timestamp_val]
                return $args[0] === 'XADD' 
                    && $args[1] === SignalDispatcher::STREAM_KEY
                    && $args[7] === 'test-node'
                    && $args[9] === 'ADD_INBOUND'
                    && is_string($args[11]); // Payload should be JSON
            })
            ->andReturn('12345-0');

        $dispatcher = new SignalDispatcher();
        $dispatcher->dispatch('test-node', 'ADD_INBOUND', ['port' => 443]);
    }

    /**
     * Test batch transaction signal contract.
     */
    public function test_batch_signal_contract()
    {
        Redis::shouldReceive('executeRaw')
            ->once()
            ->withArgs(function ($args) {
                $payload = json_decode($args[11], true);
                return $args[9] === 'BATCH_TRANSACTION'
                    && isset($payload['signals'])
                    && is_array($payload['signals']);
            })
            ->andReturn('12345-1');

        $dispatcher = new SignalDispatcher();
        $dispatcher->dispatchBatch('test-node', [['action' => 'ADD_INBOUND', 'payload' => []]]);
    }
}
