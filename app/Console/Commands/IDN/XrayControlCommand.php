<?php

namespace App\Console\Commands\IDN;

use App\Facades\Xray;
use Illuminate\Console\Command;
use App\Utils\XrayProtobufHydrator;

class XrayControlCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'idn:xray-ctl 
                            {action : stats, sys-stats, list-inbounds, ping, add-inbound, remove-inbound} 
                            {--connection=local : The Xray connection name}
                            {--tag= : Tag for remove-inbound}
                            {--port= : Port for add-inbound}';

    /**
     * The console command description.
     */
    protected $description = 'Directly interact with Xray-core gRPC API for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $connection = $this->option('connection');
        
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
}
