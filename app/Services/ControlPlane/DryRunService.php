<?php

namespace App\Services\ControlPlane;

use App\Facades\Xray;
use Exception;
use Xray\App\Proxyman\InboundHandlerConfig;

class DryRunService
{
    /**
     * Validate an inbound configuration against the dry-run instance.
     */
    public function validateInbound(InboundHandlerConfig $inbound): bool
    {
        try {
            // Apply to the dry-run instance
            Xray::connection('dry_run')->addInbound($inbound);
            
            // Immediately remove it to keep the dry-run instance clean
            Xray::connection('dry_run')->removeInbound($inbound->getTag());
            
            return true;
        } catch (Exception $e) {
            // Check if it's a port binding issue or other OS-level error
            if (str_contains($e->getMessage(), 'address already in use')) {
                throw new Exception("Dry-run failed: Port already in use on the target node OS.");
            }
            
            throw new Exception("Dry-run validation failed: " . $e->getMessage());
        }
    }
}
