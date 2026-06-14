<?php

namespace Database\Seeders;

use App\Models\Node;
use App\Models\PhysicalPort;
use App\Models\Tunnel;
use App\Models\XrayClient;
use App\Models\XrayInbound;
use App\Models\XrayOutbound;
use App\Models\XrayProtocolVless;
use App\Models\XrayProtocolVlessClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VerifyTunnelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $srv07 = Node::where('name', 'srv07')->first();
        $srv09 = Node::where('name', 'srv09')->first();

        if (!$srv07 || !$srv09) {
            $this->command->error("Nodes srv07 or srv09 not found. Run IDNNodesSeeder first.");
            return;
        }

        $port7 = PhysicalPort::updateOrCreate(
            ['node_id' => $srv07->id, 'port_number' => 21081],
            ['protocol' => 'tcp', 'status' => 'listening']
        );

        $inbound7 = XrayInbound::updateOrCreate(
            ['physical_port_id' => $port7->id],
            ['tag' => 'inbound-21081']
        );

        $vless7 = XrayProtocolVless::updateOrCreate(
            ['handler_id' => $inbound7->id, 'handler_type' => XrayInbound::class],
            ['decryption' => 'none']
        );

        $client = XrayClient::updateOrCreate(
            ['email' => 'test@idn.local'],
            ['uuid' => (string) Str::uuid()]
        );

        XrayProtocolVlessClient::updateOrCreate(
            ['vless_id' => $vless7->id, 'client_id' => $client->id],
            ['flow' => '']
        );

        $outbound9 = XrayOutbound::updateOrCreate(
            ['node_id' => $srv09->id, 'tag' => 'outbound-srv09'],
            []
        );

        Tunnel::updateOrCreate(
            ['tag' => 'test-tunnel'],
            [
                'source_node_id' => $srv07->id,
                'target_node_id' => $srv09->id,
                'inbound_id' => $inbound7->id,
                'outbound_id' => $outbound9->id,
                'port' => 21081,
                'protocol' => 'vless',
                'config' => [],
                'is_active' => true,
            ]
        );
    }
}
