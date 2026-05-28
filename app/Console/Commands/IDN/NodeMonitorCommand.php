<?php

namespace App\Console\Commands\IDN;

use App\Models\Node;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NodeMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'idn:node:monitor {--interval=30 : Check interval in seconds}';

    /**
     * The console command description.
     */
    protected $description = 'Fleet Watchdog: Monitor node heartbeats and mark inactive nodes';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\Tailscale\TailscaleService $tailscale)
    {
        $interval = (int) $this->option('interval');
        $this->info("IDN Fleet Watchdog started. Monitoring heartbeats and Tailscale status every {$interval}s...");

        while (true) {
            $this->checkNodes($tailscale);
            sleep($interval);
        }
    }

    protected function checkNodes(\App\Services\Tailscale\TailscaleService $tailscale)
    {
        try {
            $devices = $tailscale->devices();
            $onlineHostnames = [];
            foreach ($devices as $device) {
                if ($device['online'] ?? false) {
                    $onlineHostnames[] = $device['hostname'];
                }
            }

            $nodes = Node::all();
            foreach ($nodes as $node) {
                $isOnline = in_array($node->hostname, $onlineHostnames);
                
                if ($node->is_active !== $isOnline) {
                    $node->update(['is_active' => $isOnline]);
                    $status = $isOnline ? 'ONLINE' : 'OFFLINE';
                    $msg = "Tailscale Sync: Node [{$node->name}] is now {$status}";
                    $this->line($msg);
                    Log::info($msg);

                    // Trigger Failover if OFFLINE
                    if (!$isOnline) {
                        app(\App\Services\ControlPlane\ControlPlaneManager::class)->migrateTunnels($node);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Tailscale Sync Error: " . $e->getMessage());
        }

        $threshold = now()->subSeconds(90);
        // ... rest of heartbeat logic if needed ...
    }
}
