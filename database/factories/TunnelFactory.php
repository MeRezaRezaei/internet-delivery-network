<?php

namespace Database\Factories;

use App\Models\Tunnel;
use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

class TunnelFactory extends Factory
{
    protected $model = Tunnel::class;

    public function definition(): array
    {
        return [
            'source_node_id' => Node::factory(),
            'target_node_id' => Node::factory(),
            'tag' => $this->faker->unique()->slug(),
            'port' => $this->faker->numberBetween(1000, 65535),
            'protocol' => 'vless',
            'is_active' => true,
        ];
    }
}
