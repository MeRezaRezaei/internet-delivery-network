<?php

namespace Tests\Feature;

use App\Facades\Xray;
use App\Models\Node;
use App\Models\PhysicalPort;
use App\Models\XrayClient;
use App\Models\XrayInbound;
use App\Models\XrayProtocolVless;
use App\Models\XrayProtocolVlessClient;
use App\Models\XraySecurityReality;
use App\Models\XrayFallback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XrayRelationalConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_and_validate_a_complex_portal_config()
    {
        // 1. Create a Node
        $node = Node::factory()->create([
            'hostname' => 'edge-portal-ir',
            'ip' => '10.255.1.1',
        ]);

        // 2. Create a Physical Port for 443
        $port = PhysicalPort::factory()->create([
            'node_id' => $node->id,
            'port_number' => 443,
            'status' => 'reserved',
        ]);

        // 3. Create an Inbound
        $inbound = XrayInbound::create([
            'physical_port_id' => $port->id,
            'tag' => 'vless-reality-in',
        ]);

        // 4. Add VLESS Protocol
        $vless = XrayProtocolVless::create([
            'handler_id' => $inbound->id,
            'handler_type' => XrayInbound::class,
            'decryption' => 'none',
        ]);

        // 5. Add a Client
        $client = XrayClient::factory()->create();
        XrayProtocolVlessClient::create([
            'vless_id' => $vless->id,
            'client_id' => $client->id,
            'flow' => 'xtls-rprx-vision',
        ]);

        // 6. Add REALITY Security
        XraySecurityReality::create([
            'handler_id' => $inbound->id,
            'handler_type' => XrayInbound::class,
            'dest' => 'www.microsoft.com:443',
            'server_names' => 'www.microsoft.com',
            'private_key' => 'uF2i0y2k-o1daAhfv95IxOylyEtxtcSI5mLmRQ6u9GE',
            'short_ids' => '01234567',
        ]);

        // 7. Add a Fallback
        XrayFallback::create([
            'inbound_id' => $inbound->id,
            'dest_type' => 'port',
            'dest_value' => '8080',
            'xver' => 1,
        ]);

        // 8. Render Config
        $config = Xray::generateConfig($node);
        
        $this->assertIsArray($config);
        $this->assertEquals(443, $config['inbounds'][1]['port']); 
        $this->assertEquals('vless', $config['inbounds'][1]['protocol']);

        // 9. Validate using native xray -test
        $validation = Xray::validateNode($node);
        
        $this->assertTrue($validation['success'], "Xray validation failed: " . $validation['output']);
        $this->assertStringContainsString('Configuration OK', $validation['output']);
    }

    public function test_it_can_setup_a_portal_via_mission()
    {
        $node = Node::factory()->create();

        Xray::mission('portal')->setup($node, 443, 'portal-mission-tag', [
            'private_key' => 'uF2i0y2k-o1daAhfv95IxOylyEtxtcSI5mLmRQ6u9GE',
        ]);

        $this->assertDatabaseHas('xray_inbounds', ['tag' => 'portal-mission-tag']);
        $this->assertDatabaseHas('physical_ports', ['port_number' => 443, 'node_id' => $node->id]);

        $validation = Xray::validateNode($node);
        $this->assertTrue($validation['success']);
    }

    public function test_it_supports_nginx_mimic_fallback()
    {
        $node = Node::factory()->create();
        
        // Setup a VLESS-REALITY inbound on 443
        $port = PhysicalPort::create(['node_id' => $node->id, 'port_number' => 443, 'protocol' => 'tcp', 'status' => 'reserved']);
        $inbound = XrayInbound::create(['physical_port_id' => $port->id, 'tag' => 'reality-mimic']);
        
        XrayProtocolVless::create(['handler_id' => $inbound->id, 'handler_type' => XrayInbound::class]);
        
        // Add Fallback to Nginx (local port 8080)
        XrayFallback::create([
            'inbound_id' => $inbound->id,
            'name' => 'mimic.example.com',
            'dest_type' => 'port',
            'dest_value' => '8080',
        ]);

        $config = Xray::generateConfig($node);

        // Verify fallback is rendered correctly in the JSON
        $vlessInbound = collect($config['inbounds'])->firstWhere('tag', 'reality-mimic');
        $this->assertArrayHasKey('fallbacks', $vlessInbound['settings']);
        $this->assertEquals(8080, $vlessInbound['settings']['fallbacks'][0]['dest']);
        $this->assertEquals('mimic.example.com', $vlessInbound['settings']['fallbacks'][0]['name']);
    }

    public function test_it_can_render_splithttp_transport()
    {
        $node = Node::factory()->create();
        
        $port = PhysicalPort::create(['node_id' => $node->id, 'port_number' => 8443, 'protocol' => 'tcp', 'status' => 'reserved']);
        $inbound = XrayInbound::create(['physical_port_id' => $port->id, 'tag' => 'splithttp-in']);
        
        \App\Models\XrayTransportSplithttp::create([
            'handler_id' => $inbound->id,
            'handler_type' => XrayInbound::class,
            'path' => '/split',
            'host' => 'cdn.example.com',
            'max_upload_size' => 2000000,
            'max_concurrent_uploads' => 15,
        ]);

        $config = Xray::generateConfig($node);

        $inboundConfig = collect($config['inbounds'])->firstWhere('tag', 'splithttp-in');
        $this->assertArrayHasKey('streamSettings', $inboundConfig);
        $this->assertEquals('splithttp', $inboundConfig['streamSettings']['network']);
        $this->assertEquals('/split', $inboundConfig['streamSettings']['splithttpSettings']['path']);
        $this->assertEquals('cdn.example.com', $inboundConfig['streamSettings']['splithttpSettings']['host']);
        $this->assertEquals(2000000, $inboundConfig['streamSettings']['splithttpSettings']['maxUploadSize']);
        $this->assertEquals(15, $inboundConfig['streamSettings']['splithttpSettings']['maxConcurrentUploads']);
    }
}
