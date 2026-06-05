<?php

namespace App\Console\Commands\IDN;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

abstract class IDNBaseCommand extends Command
{
    /**
     * Execute a script from the infra/scripts directory.
     *
     * @param string $scriptName
     * @param array $args
     * @param string|null $interpreter
     * @return int
     */
    protected function executeInfraScript(string $scriptName, array $args = [], ?string $interpreter = null): int
    {
        $scriptPath = config('idn.paths.scripts') . '/' . $scriptName;
        
        if (!file_exists($scriptPath)) {
            $this->error("Script not found: {$scriptPath}");
            return Command::FAILURE;
        }

        $command = [];
        if ($interpreter) {
            $command[] = $interpreter;
        }
        
        $command[] = $scriptPath;
        
        foreach ($args as $arg) {
            $command[] = $arg;
        }

        $process = new Process($command);
        $process->setTimeout(3600); // 1 hour timeout for long running scripts
        
        $this->info("Executing: " . implode(' ', $command));

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error("Script failed with exit code: " . $process->getExitCode());
            return Command::FAILURE;
        }

        $this->info("Script completed successfully.");
        return Command::SUCCESS;
    }
}
