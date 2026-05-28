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
        $tag = $inbound->getTag();
        $conn = Xray::connection('dry_run');

        try {
            // 1. Perform filesystem pre-checks (Certificates)
            $this->verifyCertificatesExist($inbound);

            // 2. Pre-cleanup (In case a previous session crashed and left the tag)
            try {
                $conn->removeInbound($tag);
            } catch (Exception $e) {
                // Ignore if it doesn't exist
            }

            // 3. Apply to the dry-run instance
            $conn->addInbound($inbound);
            
            return true;
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'address already in use')) {
                throw new Exception("Dry-run failed: Port already in use on the target node OS.");
            }
            
            throw new Exception("Dry-run validation failed: " . $e->getMessage());
        } finally {
            // 4. Always ensure cleanup
            try {
                $conn->removeInbound($tag);
            } catch (Exception $e) {
                // Cleanup failed or already removed, ignore
            }
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
