<?php

namespace Tests\Feature;

use App\Facades\Tailscale;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Exception;

class TailscaleApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'tailscale.client_id' => 'test_client_id',
            'tailscale.client_secret' => 'test_client_secret',
            'tailscale.tailnet' => 'test.tailnet',
        ]);
    }

    public function test_it_can_list_devices()
    {
        Http::fake([
            'api.tailscale.com/api/v2/tailnet/test.tailnet/devices' => Http::response([
                'devices' => [
                    ['id' => '1', 'hostname' => 'node-1'],
                    ['id' => '2', 'hostname' => 'node-2'],
                ]
            ], 200),
        ]);

        $devices = Tailscale::devices();

        $this->assertCount(2, $devices);
        $this->assertEquals('node-1', $devices[0]['hostname']);
        
        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Basic ' . base64_encode('test_client_id:test_client_secret'));
        });
    }

    public function test_it_can_get_device_details()
    {
        Http::fake([
            'api.tailscale.com/api/v2/device/12345' => Http::response([
                'id' => '12345',
                'hostname' => 'test-node',
            ], 200),
        ]);

        $device = Tailscale::device('12345');

        $this->assertEquals('12345', $device['id']);
        $this->assertEquals('test-node', $device['hostname']);
    }

    public function test_it_can_create_auth_key()
    {
        Http::fake([
            'api.tailscale.com/api/v2/tailnet/test.tailnet/keys' => Http::response([
                'id' => 'key_id',
                'key' => 'tskey-auth-test',
            ], 200),
        ]);

        $capabilities = [
            'devices' => [
                'create' => [
                    'reusable' => true,
                    'tags' => ['tag:server'],
                ]
            ]
        ];

        $key = Tailscale::createAuthKey($capabilities);

        $this->assertEquals('tskey-auth-test', $key['key']);
        
        Http::assertSent(function ($request) use ($capabilities) {
            return $request['capabilities'] === $capabilities;
        });
    }

    public function test_it_can_get_acl_policy()
    {
        Http::fake([
            'api.tailscale.com/api/v2/tailnet/test.tailnet/acl' => Http::response([
                'acls' => [
                    ['action' => 'accept', 'src' => ['*'], 'dst' => ['*:*']]
                ]
            ], 200),
        ]);

        $acl = Tailscale::acl();

        $this->assertArrayHasKey('acls', $acl);
        $this->assertEquals('accept', $acl['acls'][0]['action']);
    }

    public function test_it_handles_api_errors()
    {
        Http::fake([
            'api.tailscale.com/api/v2/tailnet/test.tailnet/devices' => Http::response([
                'message' => 'Unauthorized'
            ], 401),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tailscale API request failed: Unauthorized');

        Tailscale::devices();
    }
}
