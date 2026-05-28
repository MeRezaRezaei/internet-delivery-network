<?php

namespace App\Services\ControlPlane;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class SignalDispatcher
{
    public const STREAM_KEY = 'idn:control-plane:streams:signals';

    /**
     * Dispatch a signal to the Control Plane stream for guaranteed delivery.
     * 
     * @param string $node The target node name (or 'all').
     * @param string $action The action to perform (e.g. ADD_INBOUND).
     * @param array $payload Data for the action.
     */
    public function dispatch(string $node, string $action, array $payload): string
    {
        $message = [
            'node' => $node,
            'action' => $action,
            'payload' => json_encode($payload),
            'timestamp' => now()->toIso8601String(),
        ];

        // XADD key [MAXLEN [~] count] * field value [field value ...]
        $id = Redis::executeRaw([
            'XADD', self::STREAM_KEY, 'MAXLEN', '~', '1000', 
            '*', 'node', $node, 'action', $action, 'payload', $message['payload'], 'timestamp', $message['timestamp']
        ]);

        Log::info("Control Plane Signal Dispatched: {$action} to {$node} (ID: {$id})");
        
        return $id;
    }

    /**
     * Dispatch a batch of signals transactionally (Logical batch).
     */
    public function dispatchBatch(string $node, array $signals): string
    {
        return $this->dispatch($node, 'BATCH_TRANSACTION', ['signals' => $signals]);
    }
}
