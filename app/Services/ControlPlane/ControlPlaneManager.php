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
     * Process a batch of control signals transactionally across MULTIPLE nodes.
     * Every signal in the batch is dry-run before ANY are applied to production.
     */
    public function processMultiNodeBatch(array $multiNodeBatch): void
    {
        Log::info("Control Plane: Starting MULTI-NODE TRANSACTIONAL batch.");

        try {
            // 1. Dry Run Phase (Validate all signals for all nodes)
            $hydratedNodesSignals = [];
            foreach ($multiNodeBatch as $node => $signals) {
                foreach ($signals as $index => $signalData) {
                    $action = $signalData['action'] ?? null;
                    $payload = $signalData['payload'] ?? [];
                    
                    if ($action === 'ADD_INBOUND') {
                        $inbound = \App\Utils\XrayProtobufHydrator::hydrateInbound($payload);
                        $this->dryRun->validateInbound($inbound);
                        $hydratedNodesSignals[$node][$index] = ['inbound' => $inbound];
                    }
                }
            }

            // 2. Execution Phase (Apply all)
            foreach ($multiNodeBatch as $node => $signals) {
                foreach ($signals as $index => $signalData) {
                    $action = $signalData['action'] ?? null;
                    $payload = $signalData['payload'] ?? [];

                    switch ($action) {
                        case 'ADD_INBOUND':
                            Xray::connection($node)->addInbound($hydratedNodesSignals[$node][$index]['inbound']);
                            break;
                        case 'REMOVE_INBOUND':
                            Xray::connection($node)->removeInbound($payload['tag']);
                            break;
                        default:
                            throw new Exception("Unknown action in multi-node batch: {$action}");
                    }
                }
                $this->updateNodeState($node, "MULTI_BATCH_APPLIED", "SUCCESS");
                Log::info("Control Plane: Multi-Node Batch successfully applied to {$node}.");
            }
        } catch (Exception $e) {
            Log::error("Control Plane Multi-Node Transaction Failed: " . $e->getMessage());
            foreach (array_keys($multiNodeBatch) as $node) {
                $this->updateNodeState($node, "MULTI_BATCH_FAILED", "FAILED", $e->getMessage());
            }
            throw $e;
        }
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
    public function migrateTunnels(\App\Models\IDN\Node $offlineNode): void
    {
        Log::warning("Control Plane: Initiating FAILOVER for offline node [{$offlineNode->name}].");

        // Migrate tunnels where the offline node is the target
        $targetTunnels = \App\Models\IDN\Tunnel::where('target_node_id', $offlineNode->id)->where('is_active', true)->get();

        if ($targetTunnels->isEmpty()) {
            Log::info("Control Plane: No target tunnels to migrate for [{$offlineNode->name}].");
        } else {
            // Find a healthy peer with the same role
            $peer = \App\Models\IDN\Node::where('role', $offlineNode->role)
                ->where('is_active', true)
                ->where('id', '!=', $offlineNode->id)
                ->first();

            if (!$peer) {
                Log::error("Control Plane Failover Error: No healthy peer found for role [{$offlineNode->role}] to replace target node.");
            } else {
                Log::info("Control Plane: Found healthy peer [{$peer->name}] to replace target node.");

                foreach ($targetTunnels as $tunnel) {
                    $tunnel->update(['target_node_id' => $peer->id]);
                    Log::info("Control Plane: Re-routing target tunnel [{$tunnel->tag}] to node [{$peer->name}].");
                    
                    app(SignalDispatcher::class)->dispatch($peer->name, 'ADD_INBOUND', 
                        $tunnel->config + ['tag' => $tunnel->tag, 'port' => $tunnel->port, 'protocol' => $tunnel->protocol]
                    );
                }
            }
        }

        // Migrate tunnels where the offline node is the source
        $sourceTunnels = \App\Models\IDN\Tunnel::where('source_node_id', $offlineNode->id)->where('is_active', true)->get();

        if ($sourceTunnels->isEmpty()) {
            Log::info("Control Plane: No source tunnels to migrate for [{$offlineNode->name}].");
        } else {
            // Find a healthy peer with the same role
            $peer = \App\Models\IDN\Node::where('role', $offlineNode->role)
                ->where('is_active', true)
                ->where('id', '!=', $offlineNode->id)
                ->first();

            if (!$peer) {
                Log::error("Control Plane Failover Error: No healthy peer found for role [{$offlineNode->role}] to replace source node.");
            } else {
                Log::info("Control Plane: Found healthy peer [{$peer->name}] to replace source node.");

                foreach ($sourceTunnels as $tunnel) {
                    $tunnel->update(['source_node_id' => $peer->id]);
                    Log::info("Control Plane: Re-routing source tunnel [{$tunnel->tag}] to node [{$peer->name}].");
                    
                    // The source node itself might not need a direct inbound signal in this system 
                    // based on TunnelController, but we update the association in the DB.
                }
            }
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
