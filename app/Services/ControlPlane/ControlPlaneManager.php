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
     * Process a control signal from Redis.
     */
    public function processSignal(array $signal): void
    {
        $action = $signal['action'] ?? null;
        $node = $signal['node'] ?? 'local';
        $payload = $signal['payload'] ?? [];

        Log::info("Control Plane: Processing {$action} for node {$node}");

        try {
            switch ($action) {
                case 'ADD_INBOUND':
                    $this->handleAddInbound($node, $payload);
                    break;
                case 'REMOVE_INBOUND':
                    $this->handleRemoveInbound($node, $payload);
                    break;
                default:
                    throw new Exception("Unknown action: {$action}");
            }

            // Update sync state in second Redis channel/hash
            $this->updateNodeState($node, $action, "SUCCESS");
            
        } catch (Exception $e) {
            Log::error("Control Plane Failure: " . $e->getMessage());
            $this->updateNodeState($node, $action, "FAILED", $e->getMessage());
            throw $e;
        }
    }

    protected function handleAddInbound(string $node, array $payload): void
    {
        $inbound = \App\Utils\XrayProtobufHydrator::hydrateInbound($payload);
        
        // 1. Dry Run Validation
        $this->dryRun->validateInbound($inbound);

        // 2. Real Application
        Xray::connection($node)->addInbound($inbound);
    }

    protected function handleRemoveInbound(string $node, array $payload): void
    {
        $tag = $payload['tag'] ?? throw new Exception("Missing inbound tag.");
        Xray::connection($node)->removeInbound($tag);
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
