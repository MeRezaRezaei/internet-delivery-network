<?php

namespace App\Utils;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class DNSVerifier
{
    /**
     * Capture DNS traffic on the technitium container and return analyzed results.
     */
    public static function captureAndAnalyze(string $domain, int $duration = 5): array
    {
        $pcapPath = "/tmp/dns_" . time() . ".pcap";
        
        // 1. Start tcpdump in background
        Process::run("docker exec idn-technitium tcpdump -i any -n udp port 53 -w {$pcapPath} -c 20");

        // 2. Perform a dig query
        $dig = Process::run("docker exec idn-technitium dig @localhost -p 53 {$domain}");

        // 3. Analyze with tshark
        $analysis = Process::run("docker exec idn-technitium tshark -r {$pcapPath} -T json");

        if ($analysis->failed()) {
            Log::error("DNS Verification Analysis Failed: " . $analysis->errorOutput());
            return [];
        }

        return json_decode($analysis->output(), true);
    }

    /**
     * Simple check if a domain resolves correctly through the local DNS server.
     */
    public static function verifyResolution(string $domain, string $expectedIp = null): bool
    {
        $result = Process::run("docker exec idn-technitium dig @localhost -p 53 {$domain} +short");
        $output = trim($result->output());

        if ($expectedIp) {
            return $output === $expectedIp;
        }

        return !empty($output);
    }
}
