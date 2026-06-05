<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class MockXrayFleetCommand extends Command
{
    protected $signature = 'idn:mock:fleet';
    protected $description = 'Spawns local xray run processes for mock fleet testing';

    public function handle()
    {
        $this->info('Starting Xray Mock Fleet...');

        $configs = [
            ['port' => 20001, 'config' => storage_path('app/mock_fleet/node1.json')],
            ['port' => 20002, 'config' => storage_path('app/mock_fleet/node2.json')],
            ['port' => 20003, 'config' => storage_path('app/mock_fleet/node3.json')],
        ];

        $processes = [];

        foreach ($configs as $node) {
            $configPath = $node['config'];
            
            if (!file_exists($configPath)) {
                $this->warn("Config file not found: {$configPath}. Creating a dummy one...");
                @mkdir(dirname($configPath), 0755, true);
                file_put_contents($configPath, json_encode([
                    'log' => ['loglevel' => 'warning'],
                    'inbounds' => [
                        [
                            'port' => $node['port'],
                            'protocol' => 'dokodemo-door',
                            'settings' => ['address' => '127.0.0.1', 'port' => 80]
                        ]
                    ],
                    'outbounds' => [['protocol' => 'freedom']]
                ]));
            }

            $process = new Process(['xray', 'run', '-c', $configPath]);
            $process->setTimeout(null);
            $process->start();
            
            $this->info("Started Xray node on port {$node['port']} with PID: {$process->getPid()}");
            
            $processes[] = [
                'port' => $node['port'],
                'process' => $process,
            ];
        }

        $this->info('Mock fleet is running. Press Ctrl+C to stop.');

        while (true) {
            foreach ($processes as $node) {
                if (!$node['process']->isRunning()) {
                    $this->error("Xray process on port {$node['port']} stopped unexpectedly.");
                }
            }
            sleep(1);
        }
    }
}
