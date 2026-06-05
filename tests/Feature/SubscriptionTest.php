<?php

namespace Tests\Feature;

use App\Models\SubHost;
use App\Models\Marzban\User;
use App\Models\Marzban\Proxy;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    /**
     * Test subscription endpoint for a valid user (HTML mode).
     */
    public function test_subscription_returns_html_for_browsers(): void
    {
        $user = User::where('status', 'active')->first();
        if (!$user) {
            $this->markTestSkipped('No active user found in Marzban DB.');
        }

        $response = $this->get("/sub/{$user->username}", [
            'Accept' => 'text/html',
            'User-Agent' => 'Mozilla/5.0'
        ]);

        $response->assertStatus(200);
        $response->assertSee('IDN Premium');
    }

    /**
     * Test subscription endpoint for a valid user (Base64 mode).
     */
    public function test_subscription_returns_base64_for_clients(): void
    {
        $user = User::where('status', 'active')->first();
        if (!$user) {
            $this->markTestSkipped('No active user found in Marzban DB.');
        }

        $response = $this->get("/sub/{$user->username}", [
            'User-Agent' => 'v2rayNG/1.8.5'
        ]);

        $response->assertStatus(200);
        
        $decoded = base64_decode($response->getContent());
        $this->assertNotFalse($decoded);
    }

    /**
     * Test subscription for non-existent user.
     */
    public function test_subscription_returns_404_for_invalid_user(): void
    {
        $response = $this->get("/sub/non_existent_user_12345");
        $response->assertStatus(404);
    }
}
