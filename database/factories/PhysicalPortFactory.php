<?php

namespace Database\Factories;

use App\Models\Node;
use App\Models\PhysicalPort;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhysicalPortFactory extends Factory
{
    protected $model = PhysicalPort::class;

    public function definition(): array
    {
        return [
            'node_id' => Node::factory(),
            'port_number' => $this->faker->numberBetween(1024, 65535),
            'protocol' => 'tcp',
            'status' => 'free',
        ];
    }
}
