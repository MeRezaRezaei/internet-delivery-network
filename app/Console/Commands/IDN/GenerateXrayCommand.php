<?php

namespace App\Console\Commands\IDN;

use Illuminate\Console\Command;

class GenerateXrayCommand extends IDNBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idn:generate-xray {--output= : Path to output the generated JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Xray configuration files using the infra generator script';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $args = [];
        if ($this->option('output')) {
            $args[] = '--output';
            $args[] = $this->option('output');
        }

        return $this->executeInfraScript(
            'generate_xray.py',
            $args,
            config('idn.binaries.python')
        );
    }
}
