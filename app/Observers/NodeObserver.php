<?php

namespace App\Observers;

use App\Models\Node;
use Exception;

class NodeObserver
{
    /**
     * Handle the Node "deleting" event.
     */
    public function deleting(Node $node): void
    {
        if ($node->sourceTunnels()->exists() || $node->targetTunnels()->exists()) {
            throw new Exception("CRITICAL SECURITY VIOLATION: Risk Guard prevented deletion of Node [{$node->name}] because it has active tunnels.");
        }
    }
}
