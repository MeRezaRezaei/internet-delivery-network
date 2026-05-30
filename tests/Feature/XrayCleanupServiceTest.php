<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\PhysicalPort;
use App\Models\XrayClient;
use App\Models\XrayInbound;
use App\Models\XrayOutbound;
use App\Models\XrayProtocolVless;
use App\Models\XrayProtocolVlessClient;
use App\Models\XraySecurityReality;
use App\Models\XrayFallback;
use App\Models\XraySniffingConfig;
use App\Services\Xray\XrayCleanupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XrayCleanupServiceTest extends TestCase
{
    use RefreshDatabase;

    protected XrayCleanupService $cleanupService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupService = new XrayCleanupService();
    }

    public function test_it_deep_cleans_inbound()
    {
        // 1. Setup a complex Inbound
        $node = Node::factory()->create();
        $port = PhysicalPort::factory()->create(['node_id' => $node->id]);
        $sniffing = XraySniffingConfig::create(['enabled' => true]);
        
        $inbound = XrayInbound::create([
            'physical_port_id' => $port->id,
            'tag' => 'cleanup-test-in',
            'sniffing_id' => $sniffing->id,
        ]);

        $vless = XrayProtocolVless::create([
            'handler_id' => $inbound->id,
            'handler_type' => XrayInbound::class,
        ]);

        $client = XrayClient::factory()->create();
        $vlessClient = XrayProtocolVlessClient::create([
            'vless_id' => $vless->id,
            'client_id' => $client->id,
        ]);

        $reality = XraySecurityReality::create([
            'handler_id' => $inbound->id,
            'handler_type' => XrayInbound::class,
            'dest' => 'example.com:443',
        ]);

        $fallback = XrayFallback::create([
            'inbound_id' => $inbound->id,
            'dest_value' => '8080',
        ]);

        // 2. Verify they exist in DB
        $this->assertDatabaseHas('xray_inbounds', ['id' => $inbound->id]);
        $this->assertDatabaseHas('xray_sniffing_configs', ['id' => $sniffing->id]);
        $this->assertDatabaseHas('xray_protocol_vless', ['id' => $vless->id]);
        $this->assertDatabaseHas('xray_protocol_vless_clients', ['id' => $vlessClient->id]);
        $this->assertDatabaseHas('xray_security_reality', ['id' => $reality->id]);
        $this->assertDatabaseHas('xray_fallbacks', ['id' => $fallback->id]);

        // 3. Perform Cleanup
        $this->cleanupService->cleanInbound($inbound);

        // 4. Verify they are gone
        $this->assertDatabaseMissing('xray_inbounds', ['id' => $inbound->id]);
        $this->assertDatabaseMissing('xray_sniffing_configs', ['id' => $sniffing->id]);
        $this->assertDatabaseMissing('xray_protocol_vless', ['id' => $vless->id]);
        $this->assertDatabaseMissing('xray_protocol_vless_clients', ['id' => $vlessClient->id]);
        $this->assertDatabaseMissing('xray_security_reality', ['id' => $reality->id]);
        $this->assertDatabaseMissing('xray_fallbacks', ['id' => $fallback->id]);

        // 5. Verify physical port and client still exist (they shouldn't be deleted)
        $this->assertDatabaseHas('idn_nodes', ['id' => $node->id]);
        $this->assertDatabaseHas('physical_ports', ['id' => $port->id]);
        $this->assertDatabaseHas('xray_clients', ['id' => $client->id]);
    }

    public function test_it_deep_cleans_outbound()
    {
        // 1. Setup a complex Outbound
        $node = Node::factory()->create();
        
        $outbound = XrayOutbound::create([
            'node_id' => $node->id,
            'tag' => 'cleanup-test-out',
        ]);

        $vless = XrayProtocolVless::create([
            'handler_id' => $outbound->id,
            'handler_type' => XrayOutbound::class,
        ]);

        $reality = XraySecurityReality::create([
            'handler_id' => $outbound->id,
            'handler_type' => XrayOutbound::class,
            'dest' => 'example.com:443',
        ]);

        // 2. Verify they exist in DB
        $this->assertDatabaseHas('xray_outbounds', ['id' => $outbound->id]);
        $this->assertDatabaseHas('xray_protocol_vless', ['id' => $vless->id]);
        $this->assertDatabaseHas('xray_security_reality', ['id' => $reality->id]);

        // 3. Perform Cleanup
        $this->cleanupService->cleanOutbound($outbound);

        // 4. Verify they are gone
        $this->assertDatabaseMissing('xray_outbounds', ['id' => $outbound->id]);
        $this->assertDatabaseMissing('xray_protocol_vless', ['id' => $vless->id]);
        $this->assertDatabaseMissing('xray_security_reality', ['id' => $reality->id]);
    }
}
