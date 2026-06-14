<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Node;
use App\Enums\NodeRole;

class IDNNodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nodes = [
            ['name' => 'srv01', 'hostname' => 'edge-01', 'ip' => '100.100.1.1', 'role' => NodeRole::EDGE->value],
            ['name' => 'srv03', 'hostname' => 'edge-03', 'ip' => '100.100.1.3', 'role' => NodeRole::EDGE->value],
            ['name' => 'srv04', 'hostname' => 'edge-04', 'ip' => '100.100.1.4', 'role' => NodeRole::EDGE->value],
            ['name' => 'srv07', 'hostname' => 'gateway-07', 'ip' => '100.100.1.7', 'role' => NodeRole::BRIDGE->value],
            ['name' => 'srv09', 'hostname' => 'provider-09', 'ip' => '100.100.1.9', 'role' => NodeRole::PORTAL->value],
            ['name' => 'srv10', 'hostname' => 'provider-10', 'ip' => '100.100.1.10', 'role' => NodeRole::PORTAL->value],
        ];

        foreach ($nodes as $nodeData) {
            Node::updateOrCreate(['name' => $nodeData['name']], $nodeData);
        }
    }
}
