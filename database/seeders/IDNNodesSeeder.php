<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IDN\Node;
use App\Enums\NodeRole;

class IDNNodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nodes = [
            ['name' => 'Node-DNS', 'hostname' => 'dns.local', 'role' => NodeRole::DNS->value],
            ['name' => 'Node-Bridge', 'hostname' => 'bridge.local', 'role' => NodeRole::BRIDGE->value],
            ['name' => 'Node-Edge', 'hostname' => 'edge.local', 'role' => NodeRole::EDGE->value],
        ];

        foreach ($nodes as $nodeData) {
            Node::updateOrCreate(['name' => $nodeData['name']], $nodeData);
        }
    }
}
