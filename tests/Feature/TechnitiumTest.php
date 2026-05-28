<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Facades\Technitium;
use Illuminate\Support\Facades\Http;

class TechnitiumTest extends TestCase
{
    /**
     * Test Technitium API connectivity and basic zone operations.
     */
    public function test_technitium_api_integration(): void
    {
        // Skip if technitium is not reachable
        try {
            Technitium::login();
        } catch (\Exception $e) {
            $this->markTestSkipped('Technitium server not reachable or login failed: ' . $e->getMessage());
        }

        $testZone = 'test-idn.com';

        // Cleanup if exists
        try {
            Technitium::zones()->delete($testZone);
        } catch (\Exception $e) {
            // Ignore if not exists
        }

        // 1. Create a zone
        $createResponse = Technitium::zones()->create($testZone);
        $this->assertEquals('ok', $createResponse['status']);

        // 2. List zones and verify
        $zones = Technitium::zones()->list();
        $this->assertContains($testZone, array_column($zones, 'name'));

        // 3. Add a record
        $addRecordResponse = Technitium::records()->add($testZone, 'api-test.' . $testZone, 'A', '1.2.3.4');
        $this->assertEquals('ok', $addRecordResponse['status']);

        // 4. Get record and verify
        $records = Technitium::records()->get($testZone, 'api-test.' . $testZone, 'A');
        $this->assertCount(1, $records);
        $this->assertEquals('1.2.3.4', $records[0]['rData']['ipAddress']);

        // 5. Update record
        $updateRecordResponse = Technitium::records()->update($testZone, 'api-test.' . $testZone, 'A', '5.6.7.8', '1.2.3.4');
        $this->assertEquals('ok', $updateRecordResponse['status']);

        // 6. Verify updated record
        $records = Technitium::records()->get($testZone, 'api-test.' . $testZone, 'A');
        $this->assertEquals('5.6.7.8', $records[0]['rData']['ipAddress']);

        // 7. Delete record
        $deleteRecordResponse = Technitium::records()->delete($testZone, 'api-test.' . $testZone, 'A', '5.6.7.8');
        $this->assertEquals('ok', $deleteRecordResponse['status']);

        // 8. Delete zone
        $deleteZoneResponse = Technitium::zones()->delete($testZone);
        $this->assertEquals('ok', $deleteZoneResponse['status']);
    }
}
