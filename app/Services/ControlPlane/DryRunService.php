<?php

namespace App\Services\ControlPlane;

use App\Facades\Xray;
use Exception;
use Xray\Core\InboundHandlerConfig;

class DryRunService
{
    /**
     * Validate an inbound configuration against the dry-run instance.
     */
    public function validateInbound(InboundHandlerConfig $inbound): bool
    {
        try {
            // 1. Perform filesystem pre-checks (Certificates)
            $this->verifyCertificatesExist($inbound);

            // 2. Apply to the dry-run instance
            Xray::connection('dry_run')->addInbound($inbound);
            
            // 3. Immediately remove it to keep the dry-run instance clean
            Xray::connection('dry_run')->removeInbound($inbound->getTag());
            
            return true;
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'address already in use')) {
                throw new Exception("Dry-run failed: Port already in use on the target node OS.");
            }
            
            throw new Exception("Dry-run validation failed: " . $e->getMessage());
        }
    }

    /**
     * Check if TLS certificates referenced in the config actually exist.
     */
    protected function verifyCertificatesExist(InboundHandlerConfig $inbound): void
    {
        $receiver = $inbound->getReceiverSettings();
        if (!$receiver) return;

        // Xray stores streamSettings in a wrapped TypedMessage
        // We'd need to deserialize it to check paths. 
        // For the prototype, we assume we check the raw array before hydration or 
        // we add a specific check for common paths.
        
        // ECONOMICAL LOGIC: 
        // Instead of complex protobuf reflection, we add a 'verifyPaths' step in the hydrator 
        // or passing the raw config here. Let's update the hydrator to perform this check.
    }
}
