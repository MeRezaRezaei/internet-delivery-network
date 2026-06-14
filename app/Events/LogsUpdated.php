<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LogsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public array $logs;
    public string $lastId;

    public function __construct(array $logs, string $lastId)
    {
        $this->logs = $logs;
        $this->lastId = $lastId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('idn.logs');
    }

    public function broadcastAs(): string
    {
        return 'LogsUpdated';
    }
}
