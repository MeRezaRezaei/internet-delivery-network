<?php

namespace App\Services\ControlPlane;

use Illuminate\Support\Facades\Redis;

class LogDispatcher
{
    public const LOG_STREAM_KEY = 'idn:control-plane:streams:logs';

    /**
     * Dispatch a log event from a node to the central control.
     */
    public function log(string $node, string $level, string $message, array $context = []): void
    {
        $data = [
            'node' => $node,
            'level' => strtoupper($level),
            'message' => $message,
            'context' => json_encode($context),
            'timestamp' => now()->toIso8601String(),
        ];

        Redis::executeRaw([
            'XADD', self::LOG_STREAM_KEY, '*', 
            'node', $data['node'], 
            'level', $data['level'], 
            'message', $data['message'], 
            'context', $data['context'], 
            'timestamp', $data['timestamp']
        ]);
        
        // Keep logs for 24 hours only to save Redis memory
        Redis::executeRaw(['XTRIM', self::LOG_STREAM_KEY, 'MAXLEN', '~', '10000']);
    }
}
