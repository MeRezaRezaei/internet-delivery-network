<?php

namespace Tests\Feature;

use App\Models\SubHost;
use App\Models\Marzban\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SubscriptionConsistencyTest extends TestCase
{
    /**
     * Verify that all active non-template hosts in DB are present in the subscription.
     */
    public function test_subscription_hosts_match_database(): void
    {
        // 1. Get an active user
        $user = User::where('status', 'active')->first();
        if (!$user) {
            $this->markTestSkipped('No active user found in Marzban DB.');
        }

        // Cleanup any test leftovers
        SubHost::where('address', 'test-unique-advanced.com')->delete();

        // 2. Count active non-template hosts in DB
        $dbHostCount = SubHost::where('is_active', true)->where('is_template', false)->count();

        // 3. Request subscription (Base64 mode for easier parsing)
        $response = $this->get("/sub/{$user->username}", [
            'User-Agent' => 'v2rayNG/1.8.5'
        ]);

        $response->assertStatus(200);
        $content = base64_decode($response->getContent());
        $lines = array_filter(explode("\n", $content));

        // 4. Assert count matches
        $this->assertEquals($dbHostCount, count($lines), "Subscription host count (" . count($lines) . ") does not match DB active non-template count ($dbHostCount)");

        // 5. Verify HTML mode consistency
        $responseHtml = $this->get("/sub/{$user->username}", [
            'Accept' => 'text/html',
            'User-Agent' => 'Mozilla/5.0'
        ]);

        $responseHtml->assertStatus(200);
        foreach (SubHost::where('is_active', true)->where('is_template', false)->get() as $host) {
            $responseHtml->assertSee($host->name);
        }
    }

    /**
     * Test XHTTP extra field generation accuracy.
     */
    public function test_xhttp_extra_generation_is_accurate(): void
    {
        $user = User::where('status', 'active')->first();
        
        // Create a specific host with all advanced fields
        $host = SubHost::create([
            'name' => 'Full Config Test Unique',
            'address' => 'test-very-unique-advanced.com',
            'port' => 443,
            'padding' => '123-456',
            'no_grpc_header' => true,
            'sc_max_each_post_bytes' => '1000',
            'sc_min_posts_interval_ms' => '100',
            'xmux_max_concurrency' => 32,
            'is_active' => true,
            'is_template' => false,
            'flow' => 'xtls-rprx-vision'
        ]);

        $response = $this->get("/sub/{$user->username}", [
            'User-Agent' => 'v2rayNG/1.8.5'
        ]);

        $content = base64_decode($response->getContent());
        
        // Find the line for our test host
        $lines = explode("\n", $content);
        $testLine = "";
        foreach ($lines as $line) {
            if (str_contains($line, 'test-very-unique-advanced.com')) {
                $testLine = $line;
                break;
            }
        }

        $this->assertNotEmpty($testLine, "Host test-very-unique-advanced.com not found in subscription");
        $this->assertStringContainsString('flow=xtls-rprx-vision', $testLine);

        // Parse the URI to check extra JSON
        preg_match('/extra=([^#&]+)/', $testLine, $matches);
        $extraJson = urldecode($matches[1]);
        $extra = json_decode($extraJson, true);

        $this->assertEquals('123-456', $extra['xPaddingBytes']);
        $this->assertTrue($extra['noGRPCHeader']);
        $this->assertEquals('1000', $extra['scMaxEachPostBytes']);
        $this->assertEquals(32, $extra['xmux']['maxConcurrency']);
        
        $host->delete();
    }
}
