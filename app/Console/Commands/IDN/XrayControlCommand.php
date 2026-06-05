<?php

namespace App\Console\Commands\IDN;

use App\Facades\Xray;
use Illuminate\Console\Command;
use App\Utils\XrayProtobufHydrator;
use App\Services\ControlPlane\NodeMonitorService;
use Illuminate\Support\Facades\Redis;

class XrayControlCommand extends Command
{
    protected $signature = 'idn:xray-ctl 
                            {action : stats, sys-stats, list-inbounds, ping, add-inbound, remove-inbound, list-nodes} 
                            {--connection=local : The Xray connection name}
                            {--tag= : Tag for remove-inbound}
                            {--port= : Port for add-inbound}';

    protected $description = 'Directly interact with Xray-core gRPC API for debugging';

    protected NodeMonitorService $monitor;

    public function __construct(NodeMonitorService $monitor)
    {
        parent::__construct();
        $this->monitor = $monitor;
    }

    public function handle()
    {
        $action = $this->argument('action');
        $connection = $this->option('connection');
        
        if ($action === 'list-nodes') {
            $this->listNodes();
            return Command::SUCCESS;
        }

        $this->info("Executing {$action} on connection [{$connection}]...");

        try {
            $xray = Xray::connection($connection);

            switch ($action) {
                case 'ping':
                    $this->ping($xray);
                    break;
                case 'sys-stats':
                    $this->displaySysStats($xray);
                    break;
                case 'stats':
                    $this->displayStats($xray);
                    break;
                case 'add-inbound':
                    $this->addInbound($xray);
                    break;
                case 'remove-inbound':
                    $this->removeInbound($xray);
                    break;
                default:
                    $this->error("Unknown action: {$action}");
                    return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("API Call Failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function ping($xray)
    {
        if ($xray->ping()) {
            $this->info("Connection Successful (Pong).");
        } else {
            $this->error("Connection Failed.");
        }
    }

    protected function displaySysStats($xray)
    {
        $stats = $xray->getSysStats();
        $this->table(array_keys($stats), [array_values($stats)]);
    }

    protected function displayStats($xray)
    {
        $stats = $xray->queryStats();
        $rows = [];
        foreach ($stats as $name => $value) {
            $rows[] = [$name, $value];
        }
        $this->table(['Stat Name', 'Value'], $rows);
    }

    protected function addInbound($xray)
    {
        $port = (int) $this->option('port') ?: 20000;
        $tag = "debug-socks-{$port}";
        
        $this->info("Adding SOCKS inbound on port {$port} with tag [{$tag}]...");
        
        $config = [
            'tag' => $tag,
            'port' => $port,
            'protocol' => 'socks',
        ];

        $inbound = XrayProtobufHydrator::hydrateInbound($config);
        $xray->addInbound($inbound);
        
        $this->info("Inbound added successfully.");
    }

    protected function removeInbound($xray)
    {
        $tag = $this->option('tag') ?: throw new \Exception("Tag is required for remove-inbound.");
        
        $this->info("Removing inbound [{$tag}]...");
        $xray->removeInbound($tag);
        $this->info("Inbound removed successfully.");
    }

    protected function listNodes()
    {
        $fleet = $this->monitor->getFleetStatus();
        
        $rows = [];
        foreach ($fleet as $name => $info) {
            $rows[] = [
                $name,
                $info['healthy'] ? '✅ ONLINE' : '❌ OFFLINE',
                $info['hostname'],
                $info['last_seen'],
                $info['sync_state']['status'] ?? 'N/A',
                $info['sync_state']['last_action'] ?? 'NONE',
            ];
        }

        $this->table(['Node', 'Status', 'Hostname', 'Last Heartbeat', 'Sync', 'Last Action'], $rows);
    }
}
