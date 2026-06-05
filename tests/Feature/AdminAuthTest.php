<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

use Illuminate\Foundation\Testing\WithoutMiddleware;

class AdminAuthTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test that the login page is accessible.
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/sub/admin/login');
        $response->assertStatus(200);
        $response->assertSee('IDN Admin');
    }

    /**
     * Test admin login with valid credentials from mock .env.
     */
    public function test_admin_can_login_with_valid_credentials(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        $path = '/opt/Marzban/.env';
        if (!File::exists($path)) {
            $this->markTestSkipped('Marzban .env not found for testing.');
        }

        $content = File::get($path);
        $username = null;
        $password = null;

        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^SUDO_USERNAME\s*=\s*(.*)$/', $line, $matches)) {
                $username = trim($matches[1], '"\' ');
            }
            if (preg_match('/^SUDO_PASSWORD\s*=\s*(.*)$/', $line, $matches)) {
                $password = trim($matches[1], '"\' ');
            }
        }

        $response = $this->post('/sub/admin/login', [
            'username' => $username,
            'password' => $password,
        ]);

        $response->assertRedirect('/sub/admin/dashboard');
        $this->assertEquals(true, session('is_admin'));
    }

    /**
     * Test admin login failure with invalid credentials.
     */
    public function test_admin_cannot_login_with_invalid_credentials(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        $response = $this->post('/sub/admin/login', [
            'username' => 'wrong_user',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('login');
        $this->assertNull(session('is_admin'));
    }

    /**
     * Test admin logout.
     */
    public function test_admin_can_logout(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        
        session(['is_admin' => true]);

        $response = $this->post('/sub/admin/logout');

        $response->assertRedirect('/sub/admin/login');
        $this->assertNull(session('is_admin'));
    }
}
