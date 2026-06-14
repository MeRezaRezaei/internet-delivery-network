<?php

namespace App\Console\Commands\IDN;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class TestSignalCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'idn:control-plane:test-signal 
                            {action : ADD_INBOUND, REMOVE_INBOUND} 
                            {--tag= : Tag for remove action} 
                            {--port= : Port for add action}';

    /**
     * The console command description.
     */
    protected $description = 'Push a test signal to the IDN Control Plane Redis channel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $tag = $this->option('tag') ?: 'test-tag';
        $port = (int) ($this->option('port') ?: 40000);

        $payload = [];
        if ($action === 'REMOVE_INBOUND') {
            $payload = ['tag' => $tag];
        } elseif ($action === 'ADD_INBOUND') {
            $payload = [
                'tag' => $tag,
                'port' => $port,
                'protocol' => 'socks',
            ];
        }

        $signal = [
            'action' => $action,
            'node' => 'local',
            'payload' => $payload,
        ];

        $json = json_encode($signal);
        $this->info("Pushing Signal: " . $json);
        
        Redis::publish('idn:control-plane:signals', $json);
        
        $this->info("Signal published to idn:control-plane:signals");
    }
}
