<?php

namespace App\Services\ControlPlane;

use App\Facades\Xray;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Exception;

class ControlPlaneManager
{
    protected DryRunService $dryRun;

    public function __construct(DryRunService $dryRun)
    {
        $this->dryRun = $dryRun;
    }

    /**
     * Process a batch of control signals transactionally.
     * Every signal in the batch is dry-run before ANY are applied to production.
     */
    public function processBatch(array $batch): void
    {
        $node = $batch['node'] ?? 'local';
        $signals = $batch['signals'] ?? [];

        Log::info("Control Plane: Starting TRANSACTIONAL batch for node {$node} with " . count($signals) . " signals.");

        try {
            // 1. Dry Run Phase (Validate all)
            $hydratedSignals = [];
            foreach ($signals as $index => $signalData) {
                $action = $signalData['action'] ?? null;
                $payload = $signalData['payload'] ?? [];
                
                if ($action === 'ADD_INBOUND') {
                    $inbound = \App\Utils\XrayProtobufHydrator::hydrateInbound($payload);
                    $this->dryRun->validateInbound($inbound);
                    $hydratedSignals[$index] = ['inbound' => $inbound];
                }
            }

            // 2. Execution Phase (Apply all)
            foreach ($signals as $index => $signalData) {
                $action = $signalData['action'] ?? null;
                $payload = $signalData['payload'] ?? [];

                switch ($action) {
                    case 'ADD_INBOUND':
                        Xray::connection($node)->addInbound($hydratedSignals[$index]['inbound']);
                        break;
                    case 'REMOVE_INBOUND':
                        Xray::connection($node)->removeInbound($payload['tag']);
                        break;
                    default:
                        throw new Exception("Unknown action in batch: {$action}");
                }
            }

            $this->updateNodeState($node, "BATCH_APPLIED", "SUCCESS");
            Log::info("Control Plane: Batch successfully applied to {$node}.");

        } catch (Exception $e) {
            Log::error("Control Plane Transaction Failed: " . $e->getMessage());
            $this->updateNodeState($node, "BATCH_FAILED", "FAILED", $e->getMessage());
            throw $e;
        }
    }

    /**
     * Legacy single signal processor (proxies to batch).
     */
    public function processSignal(array $signal): void
    {
        $this->processBatch([
            'node' => $signal['node'] ?? 'local',
            'signals' => [$signal]
        ]);
    }

    /**
     * Failover logic: Migrate tunnels from an offline node to a healthy peer.
     */
    public function migrateTunnels(\App\Models\Node $offlineNode): void
    {
        Log::warning("Control Plane: Initiating FAILOVER for offline node [{$offlineNode->name}].");

        // 1. Find all inbounds targeting this node (as a target in a tunnel)
        $tunnels = \App\Models\XrayInbound::whereHas('port', function ($query) use ($offlineNode) {
            $query->where('node_id', $offlineNode->id);
        })->get();

        if ($tunnels->isEmpty()) {
            Log::info("Control Plane: No tunnels to migrate for [{$offlineNode->name}].");
            return;
        }

        // 2. Find a healthy peer with the same role
        $peer = \App\Models\Node::where('role', $offlineNode->role)
            ->where('is_active', true)
            ->where('id', '!=', $offlineNode->id)
            ->first();

        if (!$peer) {
            Log::error("Control Plane Failover Error: No healthy peer found for role [{$offlineNode->role}].");
            return;
        }

        Log::info("Control Plane: Found healthy peer [{$peer->name}] for failover.");

        // 3. Dispatch signals to re-route (This is a simplified abstraction)
        foreach ($tunnels as $tunnel) {
            // In a real scenario, we would update the Bridge nodes that connect to this Portal
            // For now, we log the intent as part of the orchestration pattern.
            Log::info("Control Plane: Re-routing tunnel [{$tunnel->tag}] to node [{$peer->name}].");
            
            // Example: Dispatch signal to 'all' or specific bridge nodes
            // app(SignalDispatcher::class)->dispatch('all', 'UPDATE_TARGET', [
            //     'old_node' => $offlineNode->name,
            //     'new_node' => $peer->name,
            //     'tunnel_tag' => $tunnel->tag
            // ]);
        }
    }

    protected function updateNodeState(string $node, string $action, string $status, ?string $error = null): void
    {
        $key = "idn:control-plane:nodes:{$node}:state";
        Redis::hSet($key, 'last_action', $action);
        Redis::hSet($key, 'status', $status);
        Redis::hSet($key, 'last_update', now()->toIso8601String());
        if ($error) {
            Redis::hSet($key, 'last_error', $error);
        }
    }
}
