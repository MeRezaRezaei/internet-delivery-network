<?php

namespace App\Services\Xray;

use App\Models\XrayInbound;
use App\Models\XrayOutbound;
use App\Models\XrayProtocolVless;
use App\Models\XrayProtocolTrojan;
use Illuminate\Support\Facades\Log;

class XrayCleanupService
{
    /**
     * Deeply clean up an XrayInbound and all its nested configuration models.
     */
    public function cleanInbound(XrayInbound $inbound): void
    {
        Log::info("Starting deep cleanup for Inbound ID: {$inbound->id}, Tag: {$inbound->tag}");

        // 1. Sniffing
        if ($inbound->sniffing) {
            $inbound->sniffing->delete();
        }

        // 2. Protocols
        if ($inbound->vless) {
            $this->cleanVless($inbound->vless);
        }
        if ($inbound->trojan) {
            $this->cleanTrojan($inbound->trojan);
        }

        // 3. Transports
        $inbound->xhttp()->delete();
        $inbound->splithttp()->delete();
        $inbound->httpupgrade()->delete();
        $inbound->grpc()->delete();

        // 4. Security
        $inbound->tls()->delete();
        $inbound->reality()->delete();

        // 5. Fallbacks
        $inbound->fallbacks()->delete();

        // 6. Finally delete the inbound itself
        $inbound->delete();
        
        Log::info("Completed deep cleanup for Inbound ID: {$inbound->id}");
    }

    /**
     * Deeply clean up an XrayOutbound and all its nested configuration models.
     */
    public function cleanOutbound(XrayOutbound $outbound): void
    {
        Log::info("Starting deep cleanup for Outbound ID: {$outbound->id}, Tag: {$outbound->tag}");

        // 1. Protocols
        if ($outbound->vless) {
            $this->cleanVless($outbound->vless);
        }
        if ($outbound->trojan) {
            $this->cleanTrojan($outbound->trojan);
        }

        // 2. Transports
        $outbound->xhttp()->delete();
        $outbound->splithttp()->delete();
        $outbound->httpupgrade()->delete();
        $outbound->grpc()->delete();

        // 3. Security
        $outbound->tls()->delete();
        $outbound->reality()->delete();

        // 4. Finally delete the outbound itself
        $outbound->delete();

        Log::info("Completed deep cleanup for Outbound ID: {$outbound->id}");
    }

    /**
     * Clean up VLESS protocol and its clients.
     */
    protected function cleanVless(XrayProtocolVless $vless): void
    {
        $vless->clients()->delete();
        $vless->delete();
    }

    /**
     * Clean up Trojan protocol and its clients.
     */
    protected function cleanTrojan(XrayProtocolTrojan $trojan): void
    {
        $trojan->clients()->delete();
        $trojan->delete();
    }
}
