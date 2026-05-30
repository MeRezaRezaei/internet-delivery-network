<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LogsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public array \;
    public string \;

    public function __construct(array \, string \)
    {
        \->logs = \;
        \->lastId = \;
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
