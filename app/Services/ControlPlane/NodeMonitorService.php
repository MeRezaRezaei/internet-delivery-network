<?php

namespace App\Services\ControlPlane;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class NodeMonitorService
{
    /**
     * Get the health status of all registered nodes.
     */
    public function getFleetStatus(): array
    {
        $keys = Redis::keys("idn:control-plane:nodes:*:registry");
        $status = [];

        foreach ($keys as $key) {
            $cleanKey = str_replace(config('database.redis.options.prefix'), '', $key);
            $data = Redis::hGetAll($cleanKey);
            $nodeName = explode(':', $cleanKey)[3] ?? 'unknown';

            $lastHeartbeat = isset($data['last_heartbeat']) 
                ? Carbon::parse($data['last_heartbeat']) 
                : null;

            $isHealthy = $lastHeartbeat && $lastHeartbeat->diffInSeconds(now()) < 70;

            $status[$nodeName] = [
                'healthy' => $isHealthy,
                'hostname' => $data['hostname'] ?? 'unknown',
                'last_seen' => $lastHeartbeat ? $lastHeartbeat->diffForHumans() : 'never',
                'started_at' => $data['started_at'] ?? 'unknown',
                'sync_state' => $this->getNodeSyncState($nodeName),
            ];
        }

        return $status;
    }

    protected function getNodeSyncState(string $node): array
    {
        $key = "idn:control-plane:nodes:{$node}:state";
        return Redis::hGetAll($key) ?: ['status' => 'UNKNOWN'];
    }
}
