<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrafficUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public array $traffic;

    public function __construct(array $traffic)
    {
        $this->traffic = $traffic;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('idn.traffic');
    }

    public function broadcastAs(): string
    {
        return 'TrafficUpdated';
    }
}
