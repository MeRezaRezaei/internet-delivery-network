<?php

namespace App\Console\Commands\IDN;

use App\Models\Node;
use App\Services\ControlPlane\NodeMonitorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class FleetReconcileCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'idn:fleet:reconcile {--fix : Attempt to fix discrepancies by syncing Redis to DB}';

    /**
     * The console command description.
     */
    protected $description = 'Reconcile fleet status between Database and Redis Registry';

    /**
     * Execute the console command.
     */
    public function handle(NodeMonitorService $monitor)
    {
        $this->info("Starting Fleet Reconciliation...");

        $dbNodes = Node::all()->keyBy('name');
        $redisStatus = $monitor->getFleetStatus();

        $discrepancies = 0;
        $headers = ['Node', 'DB Status', 'Redis Status', 'DB Heartbeat', 'Redis Heartbeat', 'Match?'];
        $rows = [];

        foreach ($dbNodes as $name => $node) {
            $redis = $redisStatus[$name] ?? null;
            
            $dbActive = $node->is_active;
            $redisActive = $redis['healthy'] ?? false;
            
            $dbHb = $node->last_heartbeat_at ? $node->last_heartbeat_at->toIso8601String() : 'N/A';
            
            // Get raw heartbeat from Redis if available
            $redisRaw = Redis::hGet("idn:control-plane:nodes:{$name}:registry", 'last_heartbeat');
            $redisHb = $redisRaw ?: 'N/A';

            $matches = ($dbActive === $redisActive);

            if (!$matches) {
                $discrepancies++;
            }

            $rows[] = [
                $name,
                $dbActive ? 'ACTIVE' : 'INACTIVE',
                $redisActive ? 'HEALTHY' : 'UNHEALTHY',
                $dbHb,
                $redisHb,
                $matches ? '✅' : '❌'
            ];

            if (!$matches && $this->option('fix')) {
                $this->fixDiscrepancy($node, $redisActive, $redisRaw);
            }
        }

        // Check for nodes in Redis that are NOT in DB
        foreach ($redisStatus as $name => $redis) {
            if (!$dbNodes->has($name)) {
                $discrepancies++;
                $rows[] = [
                    $name,
                    'MISSING',
                    $redis['healthy'] ? 'HEALTHY' : 'UNHEALTHY',
                    'N/A',
                    $redis['last_seen'],
                    '❌'
                ];

                if ($this->option('fix')) {
                    $this->createMissingNode($name, $redis);
                }
            }
        }

        $this->table($headers, $rows);

        if ($discrepancies > 0) {
            $this->warn("Found {$discrepancies} discrepancies.");
        } else {
            $this->info("Fleet is in perfect sync.");
        }

        return $discrepancies > 0 ? 1 : 0;
    }

    protected function fixDiscrepancy(Node $node, bool $redisActive, ?string $redisHb)
    {
        $this->line("Fixing [{$node->name}]...");
        
        $update = ['is_active' => $redisActive];
        if ($redisHb) {
            $update['last_heartbeat_at'] = Carbon::parse($redisHb);
        }

        $node->update($update);
        $this->info("  -> Synced from Redis.");
    }

    protected function createMissingNode(string $name, array $redis)
    {
        $this->line("Creating missing node [{$name}]...");
        
        Node::create([
            'name' => $name,
            'hostname' => $redis['hostname'] ?? $name,
            'is_active' => $redis['healthy'],
            'last_heartbeat_at' => isset($redis['last_seen']) ? now() : null, // Fallback
            'status' => 'DISCOVERED',
        ]);

        $this->info("  -> Created.");
    }
}
