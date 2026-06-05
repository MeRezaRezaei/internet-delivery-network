<?php

namespace Tests\Feature\Contract;

use App\Events\LogsUpdated;
use App\Events\TrafficUpdated;
use Tests\TestCase;

class EventContractTest extends TestCase
{
    /**
     * Test that LogsUpdated event matches the expected contract for the dashboard.
     */
    public function test_logs_updated_event_contract()
    {
        $logs = [['text' => 'test log', 'level' => 'info']];
        $lastId = '12345-0';
        $event = new LogsUpdated($logs, $lastId);

        $this->assertEquals('idn.logs', $event->broadcastOn()->name);
        $this->assertEquals('LogsUpdated', $event->broadcastAs());
        $this->assertEquals($logs, $event->logs);
        $this->assertEquals($lastId, $event->lastId);
        
        // Ensure serialization includes required fields
        $serialized = json_decode(json_encode($event), true);
        $this->assertArrayHasKey('logs', $serialized);
        $this->assertArrayHasKey('lastId', $serialized);
    }

    /**
     * Test that TrafficUpdated event matches the expected contract for the dashboard.
     */
    public function test_traffic_updated_event_contract()
    {
        $traffic = ['inbound' => 1000, 'outbound' => 500];
        $event = new TrafficUpdated($traffic);

        $this->assertEquals('idn.traffic', $event->broadcastOn()->name);
        $this->assertEquals('TrafficUpdated', $event->broadcastAs());
        $this->assertEquals($traffic, $event->traffic);

        // Ensure serialization includes required fields
        $serialized = json_decode(json_encode($event), true);
        $this->assertArrayHasKey('traffic', $serialized);
    }
}
