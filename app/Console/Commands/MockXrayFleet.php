<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MockXrayFleet extends Command
{
    protected $signature = 'idn:mock:fleet {--stop : Stop the mock fleet}';
    protected $description = 'Run a local mock fleet of Xray nodes (DNS, Bridge, Edge)';
    protected $pidsFile = 'xray/mock_pids.json';

    public function handle()
    {
        if ($this->option('stop')) {
            $this->stopFleet();
            return;
        }

        $this->startFleet();
    }

    protected function startFleet()
    {
        $this->info("Generating mock Xray configurations...");

        $nodes = ['dns', 'bridge', 'edge'];
        $baseDir = storage_path('app/xray');

        if (!File::exists($baseDir)) {
            File::makeDirectory($baseDir, 0755, true);
        }

        $pids = [];

        foreach ($nodes as $index => $node) {
            $configPath = $baseDir . DIRECTORY_SEPARATOR . $node . '.json';
            $port = 10000 + $index;
            $this->generateConfig($node, $configPath, $port);

            $this->info("Starting $node node...");
            
            $binary = base_path('bin/xray');
            if (DIRECTORY_SEPARATOR === '\\') {
                $binary .= '.exe';
            }

            if (!File::exists($binary)) {
                $this->error("Xray binary not found at $binary. Please run scripts/download_xray.sh first or download it manually into bin/.");
                return;
            }

            $logFile = $baseDir . DIRECTORY_SEPARATOR . $node . '.log';
            $errFile = $baseDir . DIRECTORY_SEPARATOR . $node . '.err';

            if (DIRECTORY_SEPARATOR === '\\') {
                // Windows
                $cmd = sprintf('start /B "" "%s" run -c "%s" > "%s" 2> "%s"', $binary, $configPath, $logFile, $errFile);
                $process = proc_open($cmd, [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ], $pipes);
                
                $pids[$node] = 'windows_background'; 
            } else {
                // Linux / macOS
                $cmd = sprintf('nohup "%s" run -c "%s" > "%s" 2> "%s" < /dev/null & echo $!', $binary, $configPath, $logFile, $errFile);
                $pid = trim(shell_exec($cmd));
                if (is_numeric($pid)) {
                    $pids[$node] = (int) $pid;
                    $this->info("Started $node with PID: $pid");
                }
            }
        }

        if (!empty($pids)) {
            Storage::put($this->pidsFile, json_encode($pids, JSON_PRETTY_PRINT));
            $this->info("Fleet started. PIDs saved to " . storage_path('app/' . $this->pidsFile));
        }
    }

    protected function stopFleet()
    {
        $this->info("Stopping mock Xray fleet...");

        if (DIRECTORY_SEPARATOR === '\\') {
            exec('taskkill /F /IM xray.exe 2>NUL', $output, $returnVar);
            $this->info("Taskkill executed for xray.exe");
        } else {
            if (Storage::exists($this->pidsFile)) {
                $pids = json_decode(Storage::get($this->pidsFile), true);
                foreach ($pids as $node => $pid) {
                    if (is_numeric($pid)) {
                        exec("kill -9 $pid 2>/dev/null");
                        $this->info("Killed $node (PID: $pid)");
                    }
                }
                Storage::delete($this->pidsFile);
            } else {
                // Fallback
                exec('pkill -f "xray run -c" 2>/dev/null');
                $this->info("pkill executed for xray processes.");
            }
        }

        $this->info("Mock fleet stopped.");
    }

    protected function generateConfig($node, $configPath, $port)
    {
        // Minimal dummy Xray config
        $config = [
            'log' => [
                'loglevel' => 'warning'
            ],
            'inbounds' => [
                [
                    'port' => $port,
                    'protocol' => 'dokodemo-door',
                    'settings' => [
                        'address' => '127.0.0.1',
                        'port' => 80,
                        'network' => 'tcp'
                    ],
                    'tag' => 'inbound-' . $node
                ]
            ],
            'outbounds' => [
                [
                    'protocol' => 'freedom',
                    'tag' => 'outbound-' . $node
                ]
            ]
        ];

        File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));
    }
}