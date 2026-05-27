<?php

namespace App\Console\Commands\IDN;

use App\Services\ControlPlane\ControlPlaneManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ControlPlaneListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'idn:control-plane:listen';

    /**
     * The console command description.
     */
    protected $description = 'Listen for real-time Xray configuration signals from Redis';

    protected ControlPlaneManager $manager;

    public function __construct(ControlPlaneManager $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("IDN Control Plane Listener Started...");
        $this->info("Listening on: idn:control-plane:signals");

        // We use Redis Pub/Sub for real-time signaling
        Redis::subscribe(['idn:control-plane:signals'], function ($message) {
            $this->info("Signal Received: " . $message);
            
            try {
                $signal = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
                $this->manager->processSignal($signal);
                $this->info("Signal Processed Successfully.");
            } catch (\Exception $e) {
                $this->error("Signal Processing Failed: " . $e->getMessage());
                Log::error("Control Plane Signal Error: " . $e->getMessage());
            }
        });
    }
}
