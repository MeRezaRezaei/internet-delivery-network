<?php

namespace App\Console\Commands\IDN;

use App\Models\Tunnel;
use App\Services\Xray\XrayConfigRenderer;
use App\Services\Xray\XrayValidator;
use Illuminate\Console\Command;

class VerifyTunnelsCommand extends IDNBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idn:verify-tunnels {--tunnel= : Specific tunnel ID to verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify all active tunnels using xray -test and live connectivity checks';

    /**
     * Execute the console command.
     */
    public function handle(XrayConfigRenderer $renderer, XrayValidator $validator): int
    {
        $query = Tunnel::where('is_active', true);

        if ($this->option('tunnel')) {
            $query->where('id', $this->option('tunnel'));
        }

        $tunnels = $query->with(['sourceNode', 'targetNode', 'inbound', 'outbound'])->get();

        if ($tunnels->isEmpty()) {
            $this->info("No active tunnels found.");
            return Command::SUCCESS;
        }

        $this->info("Verifying " . $tunnels->count() . " tunnels...");

        foreach ($tunnels as $tunnel) {
            $this->verifyTunnel($tunnel, $renderer, $validator);
        }

        return Command::SUCCESS;
    }

    /**
     * Verify a specific tunnel.
     */
    protected function verifyTunnel(Tunnel $tunnel, XrayConfigRenderer $renderer, XrayValidator $validator)
    {
        $this->line("--------------------------------------------------");
        $this->info("Tunnel [{$tunnel->id}] {$tunnel->tag}: {$tunnel->sourceNode->name} -> {$tunnel->targetNode->name}");

        // 1. Validate Config for Source Node
        $this->line("1. Validating Source Node config...");
        $sourceConfig = $renderer->render($tunnel->sourceNode);
        $result = $validator->validate($sourceConfig);
        
        if ($result['success']) {
            $this->info("   [PASS] Source Node config is valid.");
        } else {
            $this->error("   [FAIL] Source Node config is invalid.");
            $this->line($result['output']);
        }

        // 2. Validate Config for Target Node
        $this->line("2. Validating Target Node config...");
        $targetConfig = $renderer->render($tunnel->targetNode);
        $result = $validator->validate($targetConfig);
        
        if ($result['success']) {
            $this->info("   [PASS] Target Node config is valid.");
        } else {
            $this->error("   [FAIL] Target Node config is invalid.");
            $this->line($result['output']);
        }

        // 3. Connectivity Check (Simple Ping for now)
        if ($tunnel->targetNode->ip) {
            $this->line("3. Checking connectivity to Target Node IP ({$tunnel->targetNode->ip})...");
            $ping = exec("ping -c 1 -W 2 {$tunnel->targetNode->ip} 2>&1", $output, $resultCode);
            if ($resultCode === 0) {
                $this->info("   [PASS] Target Node is reachable.");
            } else {
                $this->error("   [FAIL] Target Node is unreachable.");
            }
        } else {
            $this->warn("3. Skipping connectivity check: Target Node has no IP.");
        }
    }
}
