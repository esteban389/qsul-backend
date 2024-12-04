<?php

namespace Database\Factories;

use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'icon' => $this->faker->md5(),
        ];
    }

    public function withProcess(): static
    {
        return $this->state(fn (array $attributes) => [
            'process_id' => Process::query()->inRandomOrder()->value('id'),
        ]);
    }
}
