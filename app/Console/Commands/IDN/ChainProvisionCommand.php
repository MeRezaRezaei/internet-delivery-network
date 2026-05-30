<?php

namespace App\Console\Commands\IDN;

use Illuminate\Console\Command;
use App\Facades\Xray;
use App\Models\Node;

class ChainProvisionCommand extends Command
{
    protected $signature = 'idn:tunnel:chain {nodes* : Ordered list of node names/hostnames} {--port=20000 : Starting inbound port}';
    protected $description = 'Provision a single tunnel across multiple hops (Chain) in one atomic transaction';

    public function handle()
    {
        $nodeNames = $this->argument('nodes');
        $startPort = (int) $this->option('port');

        if (count($nodeNames) < 2) {
            $this->error('A chain requires at least 2 nodes.');
            return Command::FAILURE;
        }

        $hops = [];
        $currentPort = $startPort;

        foreach ($nodeNames as $index => $name) {
            $node = Node::where('hostname', $name)->orWhere('name', $name)->first();

            if (!$node) {
                // If it doesn't exist, we mock it for testing if requested or just fail.
                $this->error("Node [{$name}] not found in registry.");
                return Command::FAILURE;
            }

            $hops[] = [
                'node' => $node,
                'inbound_port' => $currentPort,
                'inbound_tag' => "chain-in-{$index}",
            ];

            $currentPort++; // Increment port for the next hop's inbound
        }

        $this->info("Provisioning Chain Mission across " . count($hops) . " nodes...");

        try {
            $result = Xray::mission('chain')->setup($hops);
            $this->info("Chain provisioned successfully.");
            $this->line("Created " . count($result['inbounds']) . " inbounds, " . count($result['outbounds']) . " outbounds, and " . count($result['tunnels']) . " IDN tunnels.");
        } catch (\Exception $e) {
            $this->error("Failed to provision chain: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
