<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Node;

class IDNNodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nodes = [
            ['name' => 'srv01', 'hostname' => 'edge-01', 'ip' => '100.100.1.1', 'role' => 'node'],
            ['name' => 'srv03', 'hostname' => 'edge-03', 'ip' => '100.100.1.3', 'role' => 'node'],
            ['name' => 'srv04', 'hostname' => 'edge-04', 'ip' => '100.100.1.4', 'role' => 'node'],
            ['name' => 'srv07', 'hostname' => 'gateway-07', 'ip' => '100.100.1.7', 'role' => 'gateway'],
            ['name' => 'srv09', 'hostname' => 'provider-09', 'ip' => '100.100.1.9', 'role' => 'provider'],
            ['name' => 'srv10', 'hostname' => 'provider-10', 'ip' => '100.100.1.10', 'role' => 'provider'],
        ];

        foreach ($nodes as $nodeData) {
            Node::updateOrCreate(['name' => $nodeData['name']], $nodeData);
        }
    }
}
