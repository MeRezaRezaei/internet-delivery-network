<?php

namespace App\Services\ControlPlane;

use App\Models\IDN\Node;
use Illuminate\Support\Facades\Log;

class RoutingEngine
{
    protected NodeMonitorService $monitor;

    public function __construct(NodeMonitorService $monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * Generate dynamic Xray routing rules based on real-time node metrics.
     */
    public function generateDynamicRules(): array
    {
        $fleetStatus = $this->monitor->getFleetStatus();
        $nodes = Node::all();

        $activeOutside = [];
        $activeInside = [];
        $activeCDN = [];

        foreach ($nodes as $node) {
            // Check both Redis status and DB is_active flag for robustness
            $redisStatus = $fleetStatus[$node->name] ?? null;
            $isHealthy = ($redisStatus && $redisStatus['healthy']) || $node->is_active;

            if ($isHealthy) {
                $nodeId = str_replace('srv', '', $node->name); // e.g. srv01 -> 01
                $nodeId = str_pad($nodeId, 2, '0', STR_PAD_LEFT);
                
                // Map roles to matrix positions
                if (str_contains($node->role, 'bridge') || str_contains($node->role, 'outside')) {
                    $activeOutside[] = $nodeId;
                }
                
                if (str_contains($node->role, 'portal') || str_contains($node->role, 'inside')) {
                    $activeInside[] = $nodeId;
                }

                if (str_contains($node->role, 'cdn')) {
                    $activeCDN[] = $nodeId;
                }
            }
        }

        // Fallbacks if empty to ensure the mesh doesn't collapse
        if (empty($activeOutside)) {
            Log::warning("RoutingEngine: No active outside nodes found. Using defaults.");
            $activeOutside = ['01', '03'];
        }
        if (empty($activeInside)) {
            Log::warning("RoutingEngine: No active inside nodes found. Using defaults.");
            $activeInside = ['01', '03', '04', '05'];
        }
        if (empty($activeCDN)) {
            Log::warning("RoutingEngine: No active CDN nodes found. Using defaults.");
            $activeCDN = ['01', '05'];
        }

        // We no longer generate the rules in PHP; we let the Python script do it
        // but we return the constraints it needs.
        return [
            'active_outside' => array_unique($activeOutside),
            'active_inside' => array_unique($activeInside),
            'active_cdn' => array_unique($activeCDN),
        ];
    }
}
