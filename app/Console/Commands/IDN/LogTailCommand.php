<?php

namespace App\Console\Commands\IDN;

use App\Services\ControlPlane\LogDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class LogTailCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'idn:log:tail {--node=all : Filter by node} {--level= : Filter by level}';

    /**
     * The console command description.
     */
    protected $description = 'Tail real-time logs from the IDN fleet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetNode = $this->option('node');
        $targetLevel = $this->option('level');

        $this->info("Tailing IDN logs... (Node: {$targetNode}, Level: " . ($targetLevel ?: 'ALL') . ")");

        $lastId = '$'; // Start from new messages only

        while (true) {
            $raw = Redis::executeRaw([
                'XREAD', 'BLOCK', '1000', 'STREAMS', LogDispatcher::LOG_STREAM_KEY, $lastId
            ]);

            if (empty($raw)) continue;

            foreach ($raw as $streamData) {
                $messages = $streamData[1] ?? [];
                foreach ($messages as $msg) {
                    $lastId = $msg[0];
                    $fields = $msg[1];
                    $data = $this->parseFields($fields);

                    if ($targetNode !== 'all' && $data['node'] !== $targetNode) continue;
                    if ($targetLevel && $data['level'] !== strtoupper($targetLevel)) continue;

                    $this->printLog($data);
                }
            }
        }
    }

    protected function parseFields(array $fields): array
    {
        $data = [];
        for ($i = 0; $i < count($fields); $i += 2) {
            $data[$fields[$i]] = $fields[$i+1];
        }
        return $data;
    }

    protected function printLog(array $data)
    {
        $time = $data['timestamp'] ?? now()->toIso8601String();
        $node = str_pad($data['node'] ?? 'unknown', 8);
        $level = str_pad($data['level'] ?? 'INFO', 7);
        $message = $data['message'] ?? '';

        $color = 'info';
        if ($level === 'ERROR  ') $color = 'error';
        if ($level === 'WARNING') $color = 'comment';

        $this->line("<{$color}>[{$time}] [{$node}] [{$level}] {$message}</{$color}>");
    }
}
