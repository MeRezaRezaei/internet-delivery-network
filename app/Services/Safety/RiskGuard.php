<?php

namespace App\Services\Safety;

use App\Models\Node;
use Exception;

class RiskGuard
{
    protected const SRV07_IP = '185.204.197.242';
    protected const SRV07_PRIVATE_IP = '10.255.1.7';
    protected const SRV07_DOMAINS = ['i-07.menudigi.ir', 'i-07.doctel.ir'];

    /**
     * Verify if a node is safe to operate on.
     */
    public function validateNodeAccess(Node $node): void
    {
        if ($this->isServer07($node)) {
            throw new Exception("CRITICAL SECURITY VIOLATION: Access to Server 07 is blocked by RiskGuard policy.");
        }
    }

    /**
     * Check if a given node is Server 07.
     */
    public function isServer07(Node $node): bool
    {
        $identifiers = [
            $node->ip,
            $node->hostname,
        ];

        foreach ($identifiers as $id) {
            if (!$id) continue;
            
            if ($id === self::SRV07_IP || $id === self::SRV07_PRIVATE_IP) {
                return true;
            }

            foreach (self::SRV07_DOMAINS as $domain) {
                if ($id === $domain || str_ends_with($id, "." . $domain)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Validate a configuration before application.
     */
    public function validateConfig(array $config): void
    {
        if (isset($config['inbounds']) && is_array($config['inbounds'])) {
            foreach ($config['inbounds'] as $inbound) {
                if (isset($inbound['port']) && in_array((int)$inbound['port'], [22, 2022], true)) {
                    throw new Exception("CRITICAL SECURITY VIOLATION: Config attempts to bind to restricted management port {$inbound['port']}.");
                }
            }
        }
    }
}
