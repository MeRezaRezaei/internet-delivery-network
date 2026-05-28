<?php

namespace Database\Factories;

use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

class NodeFactory extends Factory
{
    protected $model = Node::class;

    public function definition(): array
    {
        return [
            'hostname' => $this->faker->unique()->domainName(),
            'internal_ip' => $this->faker->unique()->ipv4(),
            'external_ip' => $this->faker->ipv4(),
            'os_type' => 'linux',
            'status' => 'active',
        ];
    }
}
