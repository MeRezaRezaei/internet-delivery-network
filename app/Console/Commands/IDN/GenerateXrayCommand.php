<?php

namespace App\Console\Commands\IDN;

use Illuminate\Console\Command;

use App\Services\ControlPlane\RoutingEngine;

class GenerateXrayCommand extends IDNBaseCommand
{
    protected $signature = 'idn:generate-xray {--output= : Path to output the generated JSON}';
    protected $description = 'Generate Xray configuration files dynamically based on real-time node metrics';

    public function handle(RoutingEngine $engine): int
    {
        $this->info("Calculating dynamic routing matrix from real-time node metrics...");
        $data = $engine->generateDynamicRules();
        
        $args = [
            '--outside=' . implode(',', $data['active_outside']),
            '--inside=' . implode(',', $data['active_inside']),
            '--cdns=' . implode(',', $data['active_cdn'])
        ];

        if ($this->option('output')) {
            $args[] = '--output';
            $args[] = $this->option('output');
        }

        $this->info("Invoking generator with dynamic constraints: " . implode(' ', $args));

        return $this->executeInfraScript(
            'generate_xray.py',
            $args,
            config('idn.binaries.python')
        );
    }
}
