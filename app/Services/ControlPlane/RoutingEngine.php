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
            $status = $fleetStatus[$node->name] ?? null;
            // Only include healthy nodes in the routing matrix
            if ($status && $status['healthy']) {
                $nodeId = str_replace('srv', '', $node->name); // e.g. srv01 -> 01
                $nodeId = str_pad($nodeId, 2, '0', STR_PAD_LEFT);
                
                if (str_contains($node->role, 'bridge') || str_contains($node->role, 'outside')) {
                    $activeOutside[] = $nodeId;
                }
                if (str_contains($node->role, 'portal') || str_contains($node->role, 'inside')) {
                    $activeInside[] = $nodeId;
                    if (str_contains($node->role, 'cdn')) {
                        $activeCDN[] = $nodeId;
                    }
                }
            }
        }

        // Fallbacks if empty
        if (empty($activeOutside)) $activeOutside = ['01', '03'];
        if (empty($activeInside)) $activeInside = ['01', '03', '04', '05'];
        if (empty($activeCDN)) $activeCDN = ['01', '05'];

        $tunnelIds = [];
        for ($i = 1; $i <= 24; $i++) {
            $tunnelIds[] = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
        }

        $routingRules = [];
        $outbounds = [];
        
        foreach ($tunnelIds as $t) {
            foreach ($activeOutside as $o) {
                foreach ($activeInside as $i) {
                    foreach ($activeCDN as $c) {
                        $tagSuffix = "{$t}_{$o}_{$i}_{$c}";
                        $outboundTag = "reverse-out-{$tagSuffix}";

                        $routingRules[] = [
                            'type' => 'field',
                            'user' => ["{$tagSuffix}@user"],
                            'outboundTag' => $outboundTag
                        ];
                    }
                }
            }
        }

        $routingRules[] = [
            'type' => 'field',
            'port' => '0-65535',
            'outboundTag' => 'BLOCK'
        ];

        return [
            'rules' => $routingRules,
            'active_outside' => $activeOutside,
            'active_inside' => $activeInside,
            'active_cdn' => $activeCDN,
            'rule_count' => count($routingRules)
        ];
    }
}
