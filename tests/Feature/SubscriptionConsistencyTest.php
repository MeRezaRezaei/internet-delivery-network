<?php

namespace Tests\Feature;

use App\Models\SubHost;
use App\Models\Marzban\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class SubscriptionConsistencyTest extends TestCase
{
    use WithoutMiddleware;

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

        // 2. Prepare controlled test data
        SubHost::query()->delete(); // Clear for exact match
        
        $h1 = SubHost::create(['name' => 'H1', 'address' => 'h1.com', 'port' => 443, 'is_active' => true, 'is_template' => false]);
        $h2 = SubHost::create(['name' => 'H2', 'address' => 'h2.com', 'port' => 443, 'is_active' => true, 'is_template' => false]);
        $h3 = SubHost::create(['name' => 'H3', 'address' => 'h3.com', 'port' => 443, 'is_active' => false, 'is_template' => false]); // Inactive
        $h4 = SubHost::create(['name' => 'H4', 'address' => 'h4.com', 'port' => 443, 'is_active' => true, 'is_template' => true]);  // Template

        // 3. Request subscription
        $response = $this->get("/sub/{$user->username}", [
            'User-Agent' => 'v2rayNG'
        ]);

        $response->assertStatus(200);
        $content = base64_decode($response->getContent());
        $lines = array_filter(explode("\n", $content));

        // 4. Assert count matches (should be 2: H1 and H2)
        $this->assertEquals(2, count($lines), "Subscription host count (" . count($lines) . ") does not match expected (2)");

        // 5. Verify HTML mode
        $responseHtml = $this->get("/sub/{$user->username}", [
            'Accept' => 'text/html',
            'User-Agent' => 'Mozilla/5.0'
        ]);

        $responseHtml->assertStatus(200);
        $responseHtml->assertSee('H1');
        $responseHtml->assertSee('H2');
        $responseHtml->assertDontSee('H3');
        $responseHtml->assertDontSee('H4');
    }

    /**
     * Test XHTTP extra field generation accuracy and Flow field.
     */
    public function test_xhttp_extra_generation_is_accurate(): void
    {
        $user = User::where('status', 'active')->first();
        SubHost::query()->delete();

        $host = SubHost::create([
            'name' => 'Advanced Host',
            'address' => 'advanced.com',
            'port' => 443,
            'padding' => '100-200',
            'no_grpc_header' => true,
            'xmux_max_concurrency' => 64,
            'flow' => 'xtls-rprx-vision',
            'is_active' => true,
            'is_template' => false
        ]);

        $response = $this->get("/sub/{$user->username}", [
            'User-Agent' => 'v2rayNG'
        ]);

        $content = base64_decode($response->getContent());
        $this->assertStringContainsString('advanced.com', $content);
        $this->assertStringContainsString('flow=xtls-rprx-vision', $content);
        
        preg_match('/extra=([^#&]+)/', $content, $matches);
        $extra = json_decode(urldecode($matches[1]), true);

        $this->assertEquals('100-200', $extra['xPaddingBytes']);
        $this->assertTrue($extra['noGRPCHeader']);
        $this->assertEquals(64, $extra['xmux']['maxConcurrency']);
    }
}
