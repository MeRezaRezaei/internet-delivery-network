<?php

namespace Tests\Feature;

use App\Facades\Xray;
use Tests\TestCase;
use Xray\App\Stats\Command\QueryStatsRequest;
use Google\Protobuf\Internal\RepeatedField;

class XrayApiTest extends TestCase
{
    /**
     * Test Xray::queryStats() through the facade.
     */
    public function test_facade_query_stats()
    {
        // This should not throw an exception if the connection is successful
        // even if it returns an empty array.
        $stats = Xray::queryStats();

        $this->assertTrue(is_array($stats) || $stats instanceof RepeatedField);
    }

    /**
     * Test direct gRPC connection using the stats client.
     */
    public function test_grpc_connection()
    {
        $request = new QueryStatsRequest();
        $request->setPattern("");
        $request->setReset(false);

        // This call will wait for the response from the gRPC server.
        // It returns [response, status].
        list($response, $status) = Xray::stats()->QueryStats($request)->wait();

        // Even if Xray returns an error (like permission denied), 
        // receiving a status code proves the gRPC communication is working.
        // Code 0 is OK, Code 14 is Unavailable (connection failed).
        
        $this->assertNotNull($status, 'gRPC status should not be null');
        
        if ($status->code === 14) {
            $this->fail("gRPC server is unavailable at 127.0.0.1:10085. Error: {$status->details}");
        }

        // If we reach here with status code 0, it's perfect.
        // Other codes might indicate configuration issues on Xray side, but gRPC is alive.
        $this->assertNotEquals(14, $status->code, "gRPC connection failed: {$status->details}");
    }

    /**
     * Test simultaneous connection to different cores.
     */
    public function test_simultaneous_multi_core_connection()
    {
        $localStats = Xray::connection('local')->queryStats();
        $secondaryStats = Xray::connection('secondary')->queryStats();

        $this->assertTrue(is_array($localStats) || $localStats instanceof RepeatedField);
        $this->assertTrue(is_array($secondaryStats) || $secondaryStats instanceof RepeatedField);
        
        // Direct verify by checking if we can switch back and forth
        $this->assertEquals('local', Xray::getDefaultDriver());
        Xray::setDefaultDriver('secondary');
        $this->assertEquals('secondary', Xray::getDefaultDriver());
        Xray::setDefaultDriver('local');
    }
}
