<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IDN\Node;

class IDNNodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nodes = [
            ['name' => 'srv01', 'hostname' => '10.255.1.1', 'role' => 'node'],
            ['name' => 'srv03', 'hostname' => '10.255.1.3', 'role' => 'node'],
            ['name' => 'srv04', 'hostname' => '10.255.1.4', 'role' => 'node'],
            ['name' => 'srv07', 'hostname' => '10.255.1.7', 'role' => 'gateway'],
            ['name' => 'srv09', 'hostname' => '10.255.1.9', 'role' => 'provider'],
            ['name' => 'srv10', 'hostname' => '10.255.1.10', 'role' => 'provider'],
        ];

        foreach ($nodes as $nodeData) {
            Node::updateOrCreate(['name' => $nodeData['name']], $nodeData);
        }
    }
}
