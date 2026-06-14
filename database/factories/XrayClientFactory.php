<?php

namespace Database\Factories;

use App\Models\XrayClient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class XrayClientFactory extends Factory
{
    protected $model = XrayClient::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'uuid' => (string) Str::uuid(),
            'secret' => Str::random(32),
        ];
    }
}
