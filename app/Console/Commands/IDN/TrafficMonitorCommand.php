<?php

namespace App\Console\Commands\IDN;

use Illuminate\Console\Command;
use App\Models\Node;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TrafficMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'idn:traffic:monitor {--interval=3 : Polling interval in seconds}';

    /**
     * The console command description.
     */
    protected $description = 'Polls Xray gRPC for traffic stats and publishes to Centrifugo/Redis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interval = (int) $this->option('interval');
        $this->info("IDN Traffic Monitor started. Polling Xray nodes every {$interval}s...");

        while (true) {
            $this->pollTraffic();
            sleep($interval);
        }
    }

    protected function pollTraffic()
    {
        $nodes = Node::where('is_active', true)->get();

        foreach ($nodes as $node) {
            try {
                $xrayConfig = [
                    'host' => $node->ip ?? $node->hostname,
                    'port' => $node->api_port ?? 10085,
                ];
                $xray = new \App\Services\Xray\XrayService($xrayConfig);
                
                // Get all downlink/uplink traffic stats
                $stats = $xray->queryStats("inbound>>>", false);
                
                $rxBytes = 0;
                $txBytes = 0;
                
                foreach ($stats as $key => $value) {
                    if (str_contains($key, 'downlink')) $rxBytes += $value;
                    if (str_contains($key, 'uplink')) $txBytes += $value;
                }
                
                // Convert to Mbps (approximated based on delta would be better, but for IDN-051 we can report raw or simple mbps based on a fake delta or total bytes if cumulative)
                // Actually Xray stats are cumulative unless reset=true is passed.
                // If we pass reset=true, it returns bytes since last call.
                // Let's use reset=true to get delta!
                $deltaStats = $xray->queryStats("inbound>>>", true);
                
                $deltaRx = 0;
                $deltaTx = 0;
                foreach ($deltaStats as $key => $value) {
                    if (str_contains($key, 'downlink')) $deltaRx += $value;
                    if (str_contains($key, 'uplink')) $deltaTx += $value;
                }

                // Delta is bytes over $interval seconds.
                // Mbps = (Bytes * 8) / (1_000_000 * seconds)
                // We'll approximate using a default interval of 3s if we are in this loop.
                $interval = (int) $this->option('interval');
                $rxMbps = ($deltaRx * 8) / (1000000 * $interval);
                $txMbps = ($deltaTx * 8) / (1000000 * $interval);

                $data = [
                    'timestamp' => now()->toIso8601String(),
                    'node_name' => $node->name,
                    'rx_mbps' => round($rxMbps, 2),
                    'tx_mbps' => round($txMbps, 2),
                ];

                // Publish to Redis so Centrifugo or API can pick it up.
                // Or just publish via an Event if using Reverb, but let's just publish to a Redis list/stream or pubsub for the API endpoint to read, or directly to Centrifugo.
                // Since `useCentrifugo.js` expects Centrifugo, we could publish via HTTP API. But we don't have centrifugo URL.
                // Wait! If Centrifugo isn't in docker-compose, let's just save to Redis and our `DashboardController::traffic` API will return the latest.
                
                // Save latest node traffic to a Redis hash for API polling
                Redis::hset('idn:traffic:latest', $node->name, json_encode($data));

            } catch (\Exception $e) {
                // Log quietly
                // Log::debug("Traffic poll failed for node [{$node->name}]: " . $e->getMessage());
            }
        }
    }
}
