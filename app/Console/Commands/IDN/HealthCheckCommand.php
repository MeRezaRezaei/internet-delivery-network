<?php

namespace App\Console\Commands\IDN;

use Illuminate\Console\Command;

class HealthCheckCommand extends IDNBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idn:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run IDN network health checks using the infra script';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->executeInfraScript(
            'idn-health-check.sh'
        );
    }
}
