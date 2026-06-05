<?php

namespace Tests\Feature;

use App\Models\SubHost;
use Tests\TestCase;

use Illuminate\Foundation\Testing\WithoutMiddleware;

class SubHostCrudTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test that guest cannot access the dashboard.
     */
    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/sub/admin/dashboard');
        $response->assertRedirect('/sub/admin/login');
    }

    /**
     * Test that admin can create a new SubHost.
     */
    public function test_admin_can_create_sub_host(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        $this->withSession(['is_admin' => true]);

        $hostData = [
            'name' => 'Test Host',
            'type' => 'direct',
            'address' => 'example.com',
            'port' => 443,
            'is_active' => true,
        ];

        $response = $this->post('/sub/admin/hosts', $hostData);

        $response->assertRedirect('/sub/admin/dashboard');
    }

    /**
     * Test that admin can update a SubHost.
     */
    public function test_admin_can_update_sub_host(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        $this->withSession(['is_admin' => true]);

        $host = SubHost::first() ?? SubHost::create([
            'name' => 'Old Name',
            'type' => 'direct',
            'address' => 'old.com',
            'port' => 443,
            'is_active' => true,
        ]);

        $response = $this->put("/sub/admin/hosts/{$host->id}", [
            'name' => 'Updated Name',
            'type' => 'reverse',
            'address' => 'updated.com',
            'port' => 8443,
            'is_active' => false,
        ]);

        $response->assertRedirect('/sub/admin/dashboard');
    }
}
