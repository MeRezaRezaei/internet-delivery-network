<?php

namespace App\Console\Commands\IDN;

use App\Services\ControlPlane\ControlPlaneManager;
use App\Services\ControlPlane\SignalDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ControlPlaneListenCommand extends Command
{
    protected $signature = 'idn:control-plane:listen 
                            {--node=local : Node identifier} 
                            {--group=control-plane-group : Consumer group name}';
    
    protected $description = 'Listen for real-time Xray configuration signals from Redis Streams';

    protected ControlPlaneManager $manager;
    protected string $nodeName;
    protected string $groupName;
    protected string $consumerName;
    protected bool $running = true;

    public function __construct(ControlPlaneManager $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    public function handle()
    {
        $this->nodeName = $this->option('node');
        $this->groupName = $this->option('group');
        $this->consumerName = gethostname() . "-{$this->nodeName}";

        $this->info("IDN Control Plane Stream Listener Started.");
        $this->info("Node: [{$this->nodeName}] | Group: [{$this->groupName}] | Consumer: [{$this->consumerName}]");

        $this->ensureGroupExists();
        $this->registerNode();

        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGINT, [$this, 'shutdown']);
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
        }

        while ($this->running) {
            try {
                // XREADGROUP GROUP group consumer [COUNT count] [BLOCK milliseconds] [NOACK] STREAMS key [key ...] ID [ID ...]
                $raw = Redis::executeRaw([
                    'XREADGROUP', 'GROUP', $this->groupName, $this->consumerName, 
                    'COUNT', '1', 'BLOCK', '5000', 
                    'STREAMS', SignalDispatcher::STREAM_KEY, '>'
                ]);

                if (empty($raw)) {
                    $this->updateHeartbeat();
                    continue;
                }

                $this->processStreamResult($raw);

            } catch (\Exception $e) {
                $this->error("Stream Reader Error: " . $e->getMessage());
                sleep(2); // Cool down
            }
        }
    }

    protected function ensureGroupExists(): void
    {
        try {
            // XGROUP CREATE key groupname id-or-$ [MKSTREAM]
            Redis::executeRaw(['XGROUP', 'CREATE', SignalDispatcher::STREAM_KEY, $this->groupName, '0', 'MKSTREAM']);
            $this->info("Consumer group created.");
        } catch (\Exception $e) {
            // Group likely already exists, ignore
        }
    }

    protected function processStreamResult(array $result): void
    {
        // Redis Streams result structure is complex in raw mode
        foreach ($result as $streamData) {
            $messages = $streamData[1] ?? [];
            foreach ($messages as $msg) {
                $id = $msg[0];
                $fields = $msg[1];
                $data = $this->parseFields($fields);

                $targetNode = $data['node'] ?? 'all';
                if ($targetNode === $this->nodeName || $targetNode === 'all') {
                    $this->info("[{$id}] Signal: {$data['action']}");
                    
                    try {
                        if ($data['action'] === 'BATCH_TRANSACTION') {
                            $payload = json_decode($data['payload'], true);
                            $this->manager->processBatch(['node' => $this->nodeName, 'signals' => $payload['signals'] ?? []]);
                        } else {
                            $this->manager->processSignal($data);
                        }
                        
                        // Acknowledge and delete (since we don't need history in the stream after successful apply)
                        Redis::executeRaw(['XACK', SignalDispatcher::STREAM_KEY, $this->groupName, $id]);
                        Redis::executeRaw(['XDEL', SignalDispatcher::STREAM_KEY, $id]);
                        
                        $this->info("Applied successfully.");
                    } catch (\Exception $e) {
                        $this->error("Apply failed: " . $e->getMessage());
                    }
                } else {
                    // Not for us, just ACK so it's not pending for this consumer
                    Redis::executeRaw(['XACK', SignalDispatcher::STREAM_KEY, $this->groupName, $id]);
                }
            }
        }
    }

    protected function parseFields(array $fields): array
    {
        $data = [];
        for ($i = 0; $i < count($fields); $i += 2) {
            $data[$fields[$i]] = $fields[$i+1];
        }
        return $data;
    }

    protected function registerNode(): void
    {
        $key = "idn:control-plane:nodes:{$this->nodeName}:registry";
        Redis::hSet($key, 'hostname', gethostname());
        Redis::hSet($key, 'started_at', now()->toIso8601String());
        
        // Update database (Auto-Discovery)
        \App\Models\IDN\Node::updateOrCreate(
            ['name' => $this->nodeName],
            [
                'hostname' => gethostname(),
                'is_active' => true,
                'last_heartbeat_at' => now(),
            ]
        );

        $this->updateHeartbeat();
    }

    protected function updateHeartbeat(): void
    {
        $key = "idn:control-plane:nodes:{$this->nodeName}:registry";
        Redis::hSet($key, 'last_heartbeat', now()->toIso8601String());
        Redis::expire($key, 60); 

        // Update database periodically (economical: every minute or so)
        // For now, every heartbeat is fine in this prototype
        \App\Models\IDN\Node::where('name', $this->nodeName)->update([
            'last_heartbeat_at' => now(),
        ]);
    }

    protected function unregisterNode(): void
    {
        Redis::del("idn:control-plane:nodes:{$this->nodeName}:registry");
        
        // Update database
        \App\Models\IDN\Node::where('name', $this->nodeName)->update([
            'is_active' => false,
        ]);
    }

    public function shutdown(): void
    {
        $this->info("Shutting down...");
        $this->running = false;
        $this->unregisterNode();
    }
}
