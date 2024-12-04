<?php

namespace Database\Factories;

use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Process>
 */
class ProcessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'icon' => $this->faker->md5(),
        ];
    }

    public function withParentProcess(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => Process::query()->inRandomOrder()->value('id'),
        ]);
    }
}
